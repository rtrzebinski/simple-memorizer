<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Jtable extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Courses');
        $this->load->model('Questions');
    }

    public function listAction() {
        switch ($this->uri->segment(3)) {
            case 'courses':
                $oUser = $this->sessionmanager->getUser();
                $this->Courses->listAction($oUser);
                break;
            case 'questions':
                $this->permissions->check(array('moduleName' => 'course', 'objectId' => $this->input->get("courseId")));
                $oCourse = new Course($this->input->get("courseId"));
                $this->Questions->listAction($oCourse);
                break;
            default:
                show_404(current_url());
        }
    }

    public function createAction() {
        switch ($this->uri->segment(3)) {
            case 'course':
                $oUser = $this->sessionmanager->getUser();
                $this->Courses->createAction($oUser);
                break;
            case 'question':
                $this->permissions->check(array('moduleName' => 'course', 'objectId' => $this->input->get("courseId")));
                $oCourse = new Course($this->input->get("courseId"));
                $this->Questions->createAction($oCourse);
                break;
            default:
                show_404(current_url());
        }
    }

    public function updateAction() {
        switch ($this->uri->segment(3)) {
            case 'course':
                $this->permissions->check(array('moduleName' => 'course', 'objectId' => $this->input->post("courseId")));
                $this->Courses->updateAction();
                break;
            case 'question':
                $this->permissions->check(array('moduleName' => 'question', 'objectId' => $this->input->post("questionId")));
                $this->Questions->updateAction();
                break;
            default:
                show_404(current_url());
        }
    }

    public function deleteAction() {
        switch ($this->uri->segment(3)) {
            case 'course':
                $this->permissions->check(array('moduleName' => 'course', 'objectId' => $this->input->post("courseId")));
                $this->Courses->deleteAction();
                break;
            case 'question':
                $this->permissions->check(array('moduleName' => 'question', 'objectId' => $this->input->post("questionId")));
                $this->Questions->deleteAction();
                break;
            default:
                show_404(current_url());
        }
    }

}