<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

get_instance()->load->iface('CreatedByUserInterface');

class Course extends CI_Model implements CreatedByUserInterface, SplObserver, SplSubject {

    const TABLE_NAME = 'course';
    private $courseId;
    private $courseName;
    private $courseName_dirty;
    private $courseQuestionsAmount;
    private $courseAveragePoints;
    private $courseAveragePoints_dirty;
    private $userId;
    private $SplSubjectObservers;
    private $SplSubjectAction;

    public function __construct($id = null) {
        parent::__construct();
        $id ? $this->setId($id) : '';
        $this->SplSubjectObservers = new SplObjectStorage();
    }

    public function getId() {
        return $this->courseId;
    }

    public function setId($courseId) {
        $this->courseId = $courseId;
    }

    public function getName() {
        if ($this->userId === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->courseName;
    }

    public function setName($courseName, $markAsDirty = true) {
        $this->courseName = trim(strip_tags($courseName));
        $this->courseName_dirty = $markAsDirty;
    }

    public function getQuestionsAmount() {
        if ($this->courseQuestionsAmount === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->courseQuestionsAmount;
    }

    public function setQuestionsAmount($courseQuestionsAmount) {
        $this->courseQuestionsAmount = $courseQuestionsAmount;
    }

    public function getAveragePoints() {
        return $this->courseAveragePoints;
    }

    /**
     * Private - available for internal usage [updateAveragePoints()] only
     * Average points can only be compute, never set from outside of object
     */
    private function setAveragePoints($courseAveragePoints) {
        $this->courseAveragePoints = $courseAveragePoints;
        $this->courseAveragePoints_dirty = true;
    }

    public function getUserId() {
        if ($this->userId === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
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
    public function create($name, $userId) {
        $this->setName($name, false);
        $this->setUserId($userId);

        $this->db->trans_start();

        $data = array(
            'courseName' => $this->courseName,
            'courseQuestionsAmount' => 0,
            'courseAveragePoints' => 0,
            'userId' => $this->userId
        );

        $this->db->insert(self::TABLE_NAME, $data);

        // notify related user
        $oUser = new User($this->getUserId());
        $this->attach($oUser);
        $this->setSplSubjectAction(__METHOD__);
        $this->notify();

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Transaction failure');
        } else {
            $this->courseId = $this->db->insert_id();
            $this->logger->logAction('course created', (array) $this);
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
            $this->courseId = $arrData['courseId'];
            $this->courseName = $arrData['courseName'];
            $this->courseQuestionsAmount = $arrData['courseQuestionsAmount'];
            $this->courseAveragePoints = $arrData['courseAveragePoints'];
            $this->userId = $arrData['userId'];
        } else if ($this->getId()) {
            $this->db->where('courseId', $this->getId());
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
            throw new Exception("Unable to load object");
        }
    }

    /**
     * Updates db if any related object field was changed
     * Handles SplSubject if provided
     * @throws Exception
     */
    public function update(SplSubject $oSubject = null) {
        if ($oSubject) {
            switch ($oSubject->getSplSubjectAction()) {
                case 'Question::create':
                    $this->increaseQuestionsAmount();
                    $this->updateAveragePoints();
                    break;
                case 'Question::delete':
                    $this->decreaseQuestionsAmount();
                    $this->updateAveragePoints();
                    break;
                case 'Question::increasePoints':
                    $this->updateAveragePoints();
                    break;
                case 'Question::decreasePoints':
                    $this->updateAveragePoints();
                    break;
                case 'Question::updateCourseId':
                    if ($oSubject->getCourseId() == $this->getId()) {
                        $this->increaseQuestionsAmount();
                        $this->updateAveragePoints();
                    } else {
                        $this->decreaseQuestionsAmount();
                        $this->updateAveragePoints();
                    }
                default:
                    break;
            }
        }

        $data = array();

        if ($this->courseName_dirty) {
            $data['courseName'] = $this->courseName;
            $this->courseName_dirty = false;
        }

        if ($this->courseAveragePoints_dirty) {
            $data['courseAveragePoints'] = $this->courseAveragePoints;
            $this->courseAveragePoints_dirty = false;
        }

        if (count($data) > 0) {
            $this->db->where('courseId', $this->getId());
            $this->db->update(self::TABLE_NAME, $data);

            if ($this->db->_error_message()) {
                throw new Exception($this->db->_error_message(), $this->db->_error_number());
            } else {
                $this->logger->logAction('course updated', (array) $this);
            }
        }
    }

    /**
     * Deletes db row
     * @throws Exception
     */
    public function delete() {
        $this->db->trans_start();

        // notify related user
        $oUser = new User($this->getUserId());
        $this->attach($oUser);
        $this->setSplSubjectAction(__METHOD__);
        $this->notify();

        // delete course from db
        $this->db->where('courseId', $this->getId());
        $this->db->delete(self::TABLE_NAME);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Transaction failure');
        } else {
            $this->logger->logAction('course deleted', (array) $this);
        }
    }

    /**
     * Updates db row, and object state
     */
    private function increaseQuestionsAmount() {
        $this->db->where('courseId', $this->getId());
        $this->db->set('courseQuestionsAmount', 'courseQuestionsAmount+1', FALSE);
        $this->db->update(self::TABLE_NAME);
        //if field was loaded update it
        $this->courseQuestionsAmount === NULL ? '' : $this->courseQuestionsAmount++;
    }

    /**
     * Updates db row, and object state
     */
    private function decreaseQuestionsAmount() {
        $this->db->where('courseId', $this->getId());
        $this->db->set('courseQuestionsAmount', 'courseQuestionsAmount-1', FALSE);
        $this->db->update(self::TABLE_NAME);
        //if field was loaded update it
        $this->courseQuestionsAmount === NULL ? '' : $this->courseQuestionsAmount--;
    }

    /**
     * Updates db row, and object state
     */
    private function updateAveragePoints() {
        $arrQuestions = $this->Questions->getQuestions($this->getId());
        $intTotalQuestions = count($arrQuestions);
        $intTotalPoints = 0;

        foreach ($arrQuestions as $oQuestion) {
            $intTotalPoints += $oQuestion->getPoints();
        }

        if ($intTotalQuestions == 0) {
            $floAveragePoints = 0;
        } else if ($intTotalQuestions == 1) {
            $floAveragePoints = $intTotalPoints;
        } else {
            $floAveragePoints = round($intTotalPoints / $intTotalQuestions, 2);
        }

        $this->setAveragePoints($floAveragePoints);
        $this->update();
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