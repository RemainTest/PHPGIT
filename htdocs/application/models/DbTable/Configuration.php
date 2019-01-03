<?php

/**
 * Configuration DB Class
 * 
 * Used for all DB access of the Configuration Table
 *  
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     CSM
 * 
 */
class Application_Model_DbTable_Configuration extends Zend_Db_Table_Abstract {

    protected $_name = 'CONFIGURATION';
    protected $_primary = array('PARAMETERS', 'PARM_VALUE');

    public function getConfig($parameters, $parm_value) {

        $row = $this->fetchRow(Array("PARAMETERS = ?" => $parameters, "PARM_VALUE = ?" => $parm_value));

        if (!$row) {
            return null;
        }
        return $row->toArray();
    }

    /**
     * Add a configuration item
     * 
     * @param string $parameters The main parameter group
     * @param string $parm_value The parameter value
     * @param string $parm_text Any text value for the parameters
     * @returns
     */
    public function addConfig($parameters, $parm_value, $parm_text) {

        $current_user = $_SERVER['REMOTE_USER'];

        $data = array('PARAMETERS' => $parameters,
            'PARM_VALUE' => $parm_value,
            'PARM_TEXT' => $parm_text,
            'ADD_USER' => $current_user,
            'CHANGE_USER' => $current_user
        );

        $this->insert($data);
    }

    /**
     * Update a configuration item
     * 
     * @param string $parameters The main parameter group
     * @param string $parm_value The parameter value
     * @param string $parm_text Any text value for the parameters
     * @returns
     */
    public function updateConfig($parameters, $parm_value, $parm_text) {

        $current_user = $_SERVER['REMOTE_USER'];

        $data = array(
            'CHANGE_USER' => $current_user,
            'PARM_TEXT' => $parm_text
        );

        $where = array(
            'PARAMETERS = ?' => $parameters,
            'PARM_VALUE = ?' => $parm_value
                );

        $this->update($data, $where);
    }

    /**
     * Delete a configuration item
     * 
     * @param string $parameters The main parameter group
     * @param string $parm_value The parameter value
     * @returns
     */
    public function deleteConfig($parameters, $parm_value) {

        $this->delete(Array("PARAMETERS = ?" => $parameters, "PARM_VALUE = ?" => $parm_value));
    }

    /**
     * Get Google Parameters
     * 
     * @returns Array|Null Array of Google Parameters, or NULL if not found
     */
    public function getGoogle() {

        $parameters = 'GOOGLE';

        $row = $this->fetchAll(Array("PARAMETERS = ?" => $parameters));

        if (!$row) {
            return NULL;
        }

        return $row->toArray();
    }

    /**
     * Get a configuration item text
     * 
     * @param string $parameters The main parameter group
     * @param string $parm_value The parameter value
     * @returns string|NULL Parameter Text value, or NULL if not found
     */
    public function getConfiguration($parameters, $parm_value) {

        $text = NULL;

        $row = $this->fetchRow(Array(
            "PARAMETERS = ?" => $parameters,
            "PARM_VALUE = ?" => $parm_value
                ));

        if ($row) {
            $text = $row ["PARM_TEXT"];
        }

        return $text;
    }

}