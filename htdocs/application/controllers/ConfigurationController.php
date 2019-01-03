<?php

class ConfigurationController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {

        // include page specific js & css
        $this->view->headScript()->appendFile('/js/configuration.js');

        $current_user = fss_init();

        $this->view->headTitle('Training');

        // Configuration Table
        $parms = new Application_Model_DbTable_Configuration();

        // Select configuration parameters for dropdown box
        $select = $parms->select()
                ->distinct(array('PARAMETERS'))
                ->from('CONFIGURATION', 'PARAMETERS')
                ->order(array('PARAMETERS'));

        $dropdown = $parms->fetchAll($select);
        $this->view->dropdown = $parms->fetchAll($select);

        foreach ($dropdown as $option => $value) {
            $drop[] = trim($value->PARAMETERS);
        }

        // populate the status drop down box
        $status_html = generateSelect('parm', $drop, '');

        $this->view->dropdown = $status_html;

        $select = $parms->select()
                ->from('CONFIGURATION', '*');

        //Selection Criteria
        $selection = $this->getRequest()->getParam('parm');
        if ($selection <> "") {
            $select->where("PARAMETERS = '$selection'");
        }

        $select->order(array('PARAMETERS', 'PARM_VALUE'));

        $this->view->configuration = $parms->fetchAll($select);
    }

    public function addAction() {

        // include page specific js & css
        $this->view->headScript()->appendFile('/js/configuration.js');

        // Select configuration parameters for dropdown box
        $parms = new Application_Model_DbTable_Configuration();
        $select = $parms->select()
                ->distinct(array('PARAMETERS'))
                ->from('CONFIGURATION', 'PARAMETERS')
                ->order(array('PARAMETERS'));

        $dropdown = $parms->fetchAll($select);
        $this->view->dropdown = $parms->fetchAll($select);

        foreach ($dropdown as $option => $value) {
            $drop[] = trim($value->PARAMETERS);
        }

        $this->view->dropdown = generateSelect('parm', $drop, '');

        if ($this->getRequest()->isPost()) {
            $key = $this->getRequest()->getParam('parm');
            $value = $this->getRequest()->getParam('value');
            $text = $this->getRequest()->getParam('text');
            $tblConfiguration = new Application_Model_DbTable_Configuration();
            $tblConfiguration->addConfig($key, $value, $text);
            //$this->_helper->redirector('index');
            $urlOptions = array('controller' => 'configuration', 'action' => 'index');
            $this->_helper->redirector($urlOptions);
        }
    }

    public function deleteAction() {

        // include page specific js & css
        $this->view->headScript()->appendFile('/js/configuration.js');

        $tblConfiguration = new Application_Model_DbTable_Configuration();

        $id = $this->getRequest()->getParam('id');

        // echo "<p>id = $id </p>" ;
        
        if ($id <> '') {

            $row = $tblConfiguration->fetchRow(Array('ID = ?' => $id));

            $parameters = $row->PARAMETERS;
            $parm_value = $row->PARM_VALUE;
            $parm_text = $row->PARM_TEXT;
            
            $config = array("parameters" => $parameters, "parm_value" => $parm_value);
            $this->view->configuration = $config;
        }

        if ($this->getRequest()->isPost()) {

            $del = $this->getRequest()->getPost('del');

            if ($del == 'Yes') {
                //$parameters = $this->getRequest()->getPost('parameters');
                //$parm_value = $this->getRequest()->getPost('parm_value');

                $tblConfiguration->deleteConfig($parameters, $parm_value);
            }

            //$this->_helper->redirector('index');
            $urlOptions = array('controller' => 'configuration', 'action' => 'index');
            $this->_helper->redirector($urlOptions);
        } else {

            $config = array("parameters" => $parameters, "parm_value" => $parm_value);
            $this->view->configuration = $config;
        }
    }

    public function updateAction() {
        
        $this->view->headScript()->appendFile('/js/configuration.js');

        $id = $this->getRequest()->getParam('id');

        $tblConfiguration = new Application_Model_DbTable_Configuration();

        $id = $this->getRequest()->getParam('id');

        //echo "<p>id = $id </p>" ;
        
        if ($id <> '') {

            $row = $tblConfiguration->fetchRow(Array('ID = ?' => $id));

            $parameters = $row->PARAMETERS;
            $parm_value = $row->PARM_VALUE;
            $parm_text = $row->PARM_TEXT;
            
            $config = array("parameters" => $parameters, "parm_value" => $parm_value);
            $this->view->configuration = $config;
            
        $this->view->id = $id;
        $this->view->key = $row->PARAMETERS;
        $this->view->value = $row->PARM_VALUE;
        $this->view->text = $row->PARM_TEXT;
        
        }

        
        if ($this->getRequest()->isPost()) {

            $key = $this->getRequest()->getParam('key');
            $value = $this->getRequest()->getParam('value');
            $text = $this->getRequest()->getParam('text');

            $tblConfiguration = new Application_Model_DbTable_Configuration();
            $tblConfiguration->updateConfig($key, $value, $text);
            $urlOptions = array('controller' => 'configuration', 'action' => 'index');
            $this->_helper->redirector($urlOptions);
        }
    }

}