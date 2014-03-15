<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

get_instance()->load->iface('CreatedByUserInterface');

class Question extends CI_Model implements CreatedByUserInterface, SplSubject {

    const TABLE_NAME = 'question';
    private $questionId;
    private $questionKey;
    private $questionKey_dirty;
    private $questionValue;
    private $questionValue_dirty;
    private $questionPoints;
    private $courseId;
    private $courseId_dirty;
    private $userId;
    private $SplSubjectObservers;
    private $SplSubjectAction;

    public function __construct($id = null) {
        parent::__construct();
        $id ? $this->setId($id) : '';
        $this->SplSubjectObservers = new SplObjectStorage();
    }

    public function getId() {
        return $this->questionId;
    }

    public function setId($questionId) {
        $this->questionId = $questionId;
    }

    public function getKey() {
        if ($this->questionKey === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->questionKey;
    }

    public function setKey($questionKey, $markAsDirty = true) {
        $this->questionKey = trim($questionKey);
        $this->questionKey_dirty = $markAsDirty;
    }

    public function getValue() {
        if ($this->questionValue === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->questionValue;
    }

    public function setValue($questionValue, $markAsDirty = true) {
        $this->questionValue = trim($questionValue);
        $this->questionValue_dirty = $markAsDirty;
    }

    public function getPoints() {
        if ($this->questionPoints === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->questionPoints;
    }

    public function getCourseId() {
        if ($this->courseId === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->courseId;
    }

    public function setCourseId($courseId, $markAsDirty = true) {
        // register previous course as observer
        if ($this->getCourseId() !== null && $this->getCourseId() != $courseId) {
            $oCourse = new Course($this->getCourseId());
            $this->attach($oCourse);
        }

        $this->courseId = $courseId;
        $this->courseId_dirty = $markAsDirty;
    }

    /**
     * Required by CreatedByUserInterface
     * @return int
     * @throws Exception
     */
    public function getUserId() {
        if ($this->userId === NULL && $this->getId() !== NULL) {
            $this->db->select('userId');
            $this->db->from(Course::TABLE_NAME);
            $this->db->join(self::TABLE_NAME, 'course.courseId = question.courseId');
            $this->db->where('questionId', $this->getId());
            $query = $this->db->get();
            $row = $query->row();
            $this->userId = $row->userId;

            if ($this->db->_error_message()) {
                throw new Exception($this->db->_error_message(), $this->db->_error_number());
            }
        }

        return $this->userId;
    }

    public function getSplSubjectAction() {
        return $this->SplSubjectAction;
    }

    public function setSplSubjectAction($SplSubjectAction) {
        $this->SplSubjectAction = $SplSubjectAction;
    }

    /**
     * Creates new db row
     * @throws Exception
     */
    public function create($key, $value, $courseId) {
        $this->setKey($key, false);
        $this->setValue($value, false);
        $this->setCourseId($courseId, false);

        $this->db->trans_start();

        $data = array(
            'questionKey' => $this->questionKey,
            'questionValue' => $this->questionValue,
            'questionPoints' => $this->config->item('maxPoints'),
            'courseId' => $this->courseId
        );

        // create question db row
        $this->db->insert(self::TABLE_NAME, $data);

        // notify related course
        $oCourse = new Course($this->getCourseId());
        $this->attach($oCourse);
        $this->setSplSubjectAction(__METHOD__);
        $this->notify();

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Transaction failure');
        } else {
            $this->questionId = $this->db->insert_id();
            $this->logger->logAction('question created', (array) $this);
        }
    }

    /**
     * Loads object fields from db, or provided array
     * @param array $arrData
     * @throws Exception
     */
    public function load(array $arrData = null) {
        // prevents from losing unsaved data
        $this->update();

        if ($arrData) {
            $this->questionId = $arrData['questionId'];
            $this->questionKey = $arrData['questionKey'];
            $this->questionValue = $arrData['questionValue'];
            $this->questionPoints = $arrData['questionPoints'];
            $this->courseId = $arrData['courseId'];
        } else if ($this->getId()) {
            $this->db->where('questionId', $this->getId());
            $query = $this->db->get(self::TABLE_NAME);

            if ($this->db->_error_message()) {
                throw new Exception($this->db->_error_message(), $this->db->_error_number());
            }

            if ($query->num_rows() == 1) {
                $this->load($query->row_array());
            } else {
                throw new Exception('Object doesn\'t exist');
            }
        } else {
            throw new Exception('Unable to load object');
        }
    }

    /**
     * Updates db if any related object field was changed
     * @throws Exception
     */
    public function update() {
        $data = array();

        if ($this->questionKey_dirty) {
            $data['questionKey'] = $this->questionKey;
            $this->questionKey_dirty = false;
        }

        if ($this->questionValue_dirty) {
            $data['questionValue'] = $this->questionValue;
            $this->questionValue_dirty = false;
        }

        if ($this->courseId_dirty) {
            // notify related course
            $oCourse = new Course($this->getCourseId());
            $this->attach($oCourse);
            $this->setSplSubjectAction(__METHOD__ . 'CourseId');

            $data['courseId'] = $this->courseId;
            $this->courseId_dirty = false;
        }

        if (count($data) > 0) {
            $this->db->trans_start();
            $this->db->where('questionId', $this->getId());
            $this->db->update(self::TABLE_NAME, $data);
            $this->notify();
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failure');
            } else {
                $this->logger->logAction('question updated', (array) $this);
            }
        }
    }

    /**
     * Deletes db row
     * @throws Exception
     */
    public function delete() {
        $this->db->trans_start();

        // notify related course
        $oCourse = new Course($this->getCourseId());
        $this->attach($oCourse);
        $this->setSplSubjectAction(__METHOD__);
        $this->notify();

        // delete question from db
        $this->db->where('questionId', $this->getId());
        $this->db->delete(self::TABLE_NAME);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Transaction failure');
        } else {
            $this->logger->logAction('question deleted', (array) $this);
        }
    }

    /**
     * Updates db row, and object state
     */
    public function increasePoints() {
        if ($this->getPoints() < $this->config->item('maxPoints')) {
            $this->db->trans_start();
            $this->db->where('questionId', $this->getId());
            $this->db->set('questionPoints', 'questionPoints+1', FALSE);
            $this->db->update(self::TABLE_NAME);

            // notify related course
            $oCourse = new Course($this->getCourseId());
            $this->attach($oCourse);
            $this->setSplSubjectAction(__METHOD__);
            $this->notify();

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failure');
            } else {
                $this->questionPoints++;
                $this->logger->logAction('question points increased', (array) $this);
            }
        }
    }

    /**
     * Updates db row, and object state
     */
    public function decreasePoints() {
        if ($this->getPoints() > $this->config->item('minPoints')) {
            $this->db->trans_start();
            $this->db->where('questionId', $this->getId());
            $this->db->set('questionPoints', 'questionPoints-1', FALSE);
            $this->db->update(self::TABLE_NAME);

            // notify related course
            $oCourse = new Course($this->getCourseId());
            $this->attach($oCourse);
            $this->setSplSubjectAction(__METHOD__);
            $this->notify();

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failure');
            } else {
                $this->questionPoints--;
                $this->logger->logAction('question points decreased', (array) $this);
            }
        }
    }

    public function attach(SplObserver $oObserver) {
        $this->SplSubjectObservers->attach($oObserver);
    }

    public function detach(SplObserver $oObserver) {
        $this->SplSubjectObservers->detach($oObserver);
    }

    public function notify() {
        foreach ($this->SplSubjectObservers as $oObserver) {
            $oObserver->update($this);
        }
        $this->setSplSubjectAction(null);
    }

}