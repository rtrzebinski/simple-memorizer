<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Front extends CI_Controller {

    private $oUser;
    private $arrPageHeaderData;

    public function __construct() {
        parent::__construct();
        $this->oUser = $this->sessionmanager->getUser();
        $this->arrPageHeaderData = array();
        $this->arrPageHeaderData['userId'] = $this->oUser->getId();
        $this->arrPageHeaderData['userName'] = $this->oUser->getName();
    }

    /**
     * Main page, shows courses list of logged user
     */
    public function index() {
        $this->load->view('header_view', $this->arrPageHeaderData);
        if ($this->oUser->getId()) {
            $this->load->view('courses_table_view');
        }
        $this->load->view('footer_view');
    }

    /**
     * Course manager (table)
     * @param int $intCourseId
     */
    public function manageCourse($intCourseId) {
        $this->load->model('Course');
        $this->load->model('Courses');

        // Super admin or course author only
        try {
            $this->permissions->check(array('moduleName' => 'course', 'objectId' => $intCourseId));
        } catch (AccessControlException $exc) {
            $this->logger->logException($exc);
            redirect("/");
        }

        // courses merge
        if ($this->input->post('merge') && is_numeric($this->input->post('courseId'))) {
            $this->Courses->mergeCourses($this->input->post('courseId'), $intCourseId);
        }

        $oCourse = new Course($intCourseId);

        $data = array();
        $data['intCourseId'] = $oCourse->getId();
        $data['strCourseName'] = $oCourse->getName();
        $data['intCourseQuestionsAmount'] = $oCourse->getQuestionsAmount();
        $data['oRemainingCourses'] = $this->Courses->getCourses($this->oUser->getId(), $intCourseId);

        $this->load->view('header_view', $this->arrPageHeaderData);
        $this->load->view('questions_table_view', $data);
        $this->load->view('footer_view');
    }

    /**
     * Learning mode
     * @param int $intCourseId
     */
    public function runCourse($intCourseId) {
        $this->load->model('Course');
        $this->load->model('Questions');
        $data = array();

        // Super admin or course author only
        try {
            $this->permissions->check(array('moduleName' => 'course', 'objectId' => $intCourseId));
        } catch (AccessControlException $exc) {
            $this->logger->logException($exc);
            redirect("/");
        }

        $oQuestion = new Question($this->input->post('questionId'));
        $oCourse = new Course($intCourseId);

        if ($this->input->post(ANSWER_GOOD)) {
            $oQuestion->decreasePoints();
        }

        if ($this->input->post(ANSWER_BAD)) {
            $oQuestion->increasePoints();
        }

        if ($this->input->post('update')) {
            $oQuestion->setKey($this->input->post('questionKey'));
            $oQuestion->setValue($this->input->post('questionValue'));
            $oQuestion->update();
            $data['hideQuestionValue'] = false;
        } else {
            $oQuestion = $this->Questions->getRandomQuestion($oCourse);
            $data['hideQuestionValue'] = true;
        }

        $this->load->view('header_view', $this->arrPageHeaderData);

        // show question view if course has at least one question
        if ($oCourse->getQuestionsAmount() > 0) {
            $data['courseId'] = $oCourse->getId();
            $data['courseName'] = $oCourse->getName();
            $data['courseAveragePoints'] = $this->pointsconverter->pointsToString($oCourse->getAveragePoints());
            $data['questionId'] = $oQuestion->getId();
            $data['questionKey'] = $oQuestion->getKey();
            $data['questionValue'] = $oQuestion->getValue();

            $this->load->view('question_view', $data);
        } else {
            $data['strMessage'] = "This course doesn't contain any questions.";
            $data['strMessage'] .= "</br>";
            $data['strMessage'] .= "Click <a href=\"/course/manage/$intCourseId\">here</a> to manage.";
            $this->load->view('message_view', $data);
        }

        $this->load->view('footer_view');
    }

}