<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class User extends CI_Model implements SplObserver {
    
    const TABLE_NAME = 'user';
    private $userId;
    private $userName;
    private $userEmail;
    private $userOauthId;
    private $userOauthProvider;
    private $userCoursesAmount;
    private $userPermissions;

    public function __construct($id = null) {
        parent::__construct();
        $id ? $this->setId($id) : '';
    }

    public function getId() {
        return $this->userId;
    }

    public function setId($userId) {
        $this->userId = $userId;
    }

    public function getName() {
        if ($this->userName === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->userName;
    }

    public function setName($userName) {
        $this->userName = $userName;
    }

    public function getEmail() {
        if ($this->userEmail === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->userEmail;
    }

    public function setEmail($userEmail) {
        $this->userEmail = $userEmail;
    }

    public function setOauthId($userOauthId) {
        $this->userOauthId = $userOauthId;
    }

    public function setOauthProvider($userOauthProvider) {
        $this->userOauthProvider = $userOauthProvider;
    }

    public function getCoursesAmount() {
        if ($this->userCoursesAmount === NULL && $this->getId() !== NULL) {
            $this->load();
        }

        return $this->userCoursesAmount;
    }

    public function setCoursesAmount($userCoursesAmount) {
        $this->userCoursesAmount = $userCoursesAmount;
    }

    public function getPermissions() {
        return $this->userPermissions;
    }

    public function setPermissions($userPermissions) {
        $this->userPermissions = $userPermissions;
    }

    public function authorizeOauth($oauthId, $oauthProvider) {
        $this->db->where('userOauthId', $oauthId);
        $this->db->where('userOauthProvider', $oauthProvider);
        $query = $this->db->get(self::TABLE_NAME);

        if ($this->db->_error_message()) {
            throw new Exception($this->db->_error_message(), $this->db->_error_number());
        }

        if ($query->num_rows() == 1) {
            foreach ($query->result_array() as $row) {
                $this->load($row);
                $this->logger->logAction('user auth', array((array) $this));
            }
        } else {
            throw new Exception("Auth failed", 401);
        }
    }

    /**
     * Creates new db row
     * @throws Exception
     */
    public function create($name, $email, $oauthId, $oauthProvider, $permissions = 0) {
        $this->setName($name);
        $this->setEmail($email);
        $this->setOauthId($oauthId);
        $this->setOauthProvider($oauthProvider);
        $this->setPermissions($permissions);
        
        $data = array(
            'userName' => $this->userName,
            'userEmail' => $this->userEmail,
            'userOauthId' => $this->userOauthId,
            'userOauthProvider' => $this->userOauthProvider,
            'userCoursesAmount' => 0,
            'userPermissions' => $this->userPermissions
        );

        $this->db->insert(self::TABLE_NAME, $data);

        if ($this->db->_error_message()) {
            throw new Exception($this->db->_error_message(), $this->db->_error_number());
        } else {
            $this->userId = $this->db->insert_id();
            $this->logger->logAction('user created', (array) $this);
        }
    }

    /**
     * Loads object fields from db, or provided array
     * @param array $arrData
     * @throws Exception
     */
    public function load($arrData = null) {
        // prevents from losing unsaved data
        $this->update();

        if ($arrData) {
            $this->userId = $arrData['userId'];
            $this->userName = $arrData['userName'];
            $this->userEmail = $arrData['userEmail'];
            $this->userOauthId = $arrData['userOauthId'];
            $this->userOauthProvider = $arrData['userOauthProvider'];
            $this->userCoursesAmount = $arrData['userCoursesAmount'];
            $this->userPermissions = $arrData['userPermissions'];
        } else if ($this->getId()) {
            $this->db->where('userId', $this->getId());
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

    public function update(SplSubject $oSubject = null) {
        if ($oSubject) {
            switch ($oSubject->getSplSubjectAction()) {
                case 'Course::create':
                    $this->increaseCoursesAmount();
                    break;
                case 'Course::delete':
                    $this->decreaseCoursesAmount();
                    break;
                default:
                    break;
            }
        }
    }

    private function increaseCoursesAmount() {
        $this->db->where('userId', $this->getId());
        $this->db->set('userCoursesAmount', 'userCoursesAmount+1', FALSE);
        $this->db->update(self::TABLE_NAME);
        //if field was loaded update it
        $this->userCoursesAmount === NULL ? '' : $this->userCoursesAmount++;
    }

    private function decreaseCoursesAmount() {
        $this->db->where('userId', $this->getId());
        $this->db->set('userCoursesAmount', 'userCoursesAmount-1', FALSE);
        $this->db->update(self::TABLE_NAME);
        //if field was loaded update it
        $this->userCoursesAmount === NULL ? '' : $this->userCoursesAmount--;
    }

}