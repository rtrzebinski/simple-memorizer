<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Questions extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Question');
    }

    /**
     * Returns questions collection
     * @param int $intCourseId
     * @return SplObjectStorage
     */
    public function getQuestions($intCourseId = null) {
        if ($intCourseId) {
            $this->db->where('courseId', $intCourseId);
        }
        $this->db->from('question');
        $query = $this->db->get();

        $oQuestions = new SplObjectStorage();

        foreach ($query->result_array() as $row) {
            $oQuestion = new Question();
            $oQuestion->load($row);
            $oQuestions->attach($oQuestion);
        }

        return $oQuestions;
    }

    /**
     * Returns random question of given course
     * Algorithm uses questions points (so it's not really random)
     * More points == bigger chance to get
     * @param Course $oCourse
     * @return Question
     */
    public function getRandomQuestion(Course $oCourse = null) {
        // Get id of random question - query1
        $this->db->where('courseId', $oCourse->getId());
        $query1 = $this->db->get('question');

        $arrQuestionId = array();

        foreach ($query1->result() as $row) {
            for ($i = $row->questionPoints; $i > 0; $i--) {
                $arrQuestionId[] = $row->questionId;
            }
        }

        if (count($arrQuestionId) > 0) {
            shuffle($arrQuestionId);
            $intRandomKey = array_rand($arrQuestionId);
            $questionId = $arrQuestionId[$intRandomKey];

            // Get question object - query2
            $query2 = $this->db->get_where('question', array('questionId' => $questionId));

            foreach ($query2->result_array() as $row) {
                $question = new Question();
                $question->load($row);
            }

            return $question;
        }
    }

    // JTable CRUD start

    /**
     * Used for AJAX calls, prints JSON
     * @param Course $oCourse
     */
    public function listAction(Course $oCourse) {
        //Get record count
        $this->db->from('question');
        $this->db->where('courseId', $oCourse->getId());
        $recordCount = $this->db->count_all_results();

        //Get records from database
        $this->db->from('question');
        $this->db->where('courseId', $oCourse->getId());
        $this->db->order_by($this->input->get('jtSorting'));
        $this->db->limit($this->input->get('jtPageSize'), $this->input->get('jtStartIndex'));
        $query = $this->db->get();
        $rows = array();
        foreach ($query->result_array() as $row) {
            $row['questionPoints'] = $this->pointsconverter->pointsToString($row['questionPoints']) . ' ' . $row['questionPoints'];
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
     * @param Course $oCourse
     */
    public function createAction(Course $oCourse) {
        //Insert record into database
        $oQuestion = new Question();
        $strQuestionKey = $this->input->post('questionKey');
        $strQuestionValue = $this->input->post('questionValue');
        $intCourseId = $oCourse->getId();
        $oQuestion->create($strQuestionKey, $strQuestionValue, $intCourseId);

        //Get last inserted record
        $this->db->from('question');
        $this->db->where('questionId', $oQuestion->getId());
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
        $oQuestion = new Question($this->input->post('questionId'));
        $oQuestion->delete();

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
        $oQuestion = new Question($this->input->post('questionId'));
        $oQuestion->setKey($this->input->post('questionKey'));
        $oQuestion->setValue($this->input->post('questionValue'));
        $oQuestion->update();

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    }

    // JTable CRUD end    
}