<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Courses extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Course');
    }

    public function mergeCourses($intSourceCourseId, $intDestinationCourseId) {
        $this->load->model('Questions');
        $arrQuestionsToMerge = $this->Questions->getQuestions($intSourceCourseId);
        if (count($arrQuestionsToMerge) > 0) {
            foreach ($arrQuestionsToMerge as $oQuestion) {
                $oQuestion->setCourseId($intDestinationCourseId);
                $oQuestion->update();
            }
            $oSourceCourse = new Course($intSourceCourseId);
            $oSourceCourse->delete();
            $this->logger->logAction('courses merged', array('sourceCourseId' => $intSourceCourseId, 'destinationCourseId' => $intDestinationCourseId));
        }
    }

    /**
     * Returns courses collection
     * @param int $intUserId
     * @param int $intExcludedCourseId
     * @return SplObjectStorage
     */
    public function getCourses($intUserId = null, $intExcludedCourseId = null) {
        if ($intUserId) {
            $this->db->where('userId', $intUserId);
        }
        if ($intExcludedCourseId) {
            $this->db->where('courseId !=', $intExcludedCourseId);
        }
        $this->db->from('course');
        $query = $this->db->get();

        $oCourses = new SplObjectStorage();

        foreach ($query->result_array() as $row) {
            $oCourse = new Course();
            $oCourse->load($row);
            $oCourses->attach($oCourse);
        }

        return $oCourses;
    }

    // JTable CRUD start

    /**
     * Used for AJAX calls, prints JSON
     * @param User $oUser
     */
    public function listAction(User $oUser) {
        //Get record count
        $this->db->from('course');
        $this->db->where('userId', $oUser->getId());
        $recordCount = $this->db->count_all_results();

        //Get records from database
        $this->db->from('course');
        $this->db->where('userId', $oUser->getId());
        $this->db->order_by($this->input->get('jtSorting'));
        $this->db->limit($this->input->get('jtPageSize'), $this->input->get('jtStartIndex'));
        $query = $this->db->get();
        $rows = array();
        foreach ($query->result_array() as $row) {
            $row['courseAveragePoints'] = $this->pointsconverter->pointsToString($row['courseAveragePoints']);
            $rows[] = $row;
        }

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['TotalRecordCount'] = $recordCount;
        $jTableResult['Records'] = $rows;
        print json_encode($jTableResult);
    }

    /**
     * Used for AJAX calls, prints JSON
     * @param User $oUser
     */
    public function createAction(User $oUser) {
        //Insert record into database
        $oCourse = new Course();
        $strCourseName = $this->input->post('courseName');
        $oCourse->create($strCourseName, $oUser->getId());

        //Get last inserted record
        $this->db->from('course');
        $this->db->where('courseId', $oCourse->getId());
        $query = $this->db->get();
        $row = $query->row();

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Record'] = $row;
        print json_encode($jTableResult);
    }

    /**
     * Used for AJAX calls, prints JSON
     */
    public function deleteAction() {
        //Delete from database
        $oCourse = new Course($this->input->post('courseId'));
        $oCourse->delete();

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    }

    /**
     * Used for AJAX calls, prints JSON
     */
    public function updateAction() {
        //Update record in database
        $oCourse = new Course($this->input->post('courseId'));
        $oCourse->setName($this->input->post('courseName'));
        $oCourse->update();

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    }

    // JTable CRUD end    
}