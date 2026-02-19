<?php

class Gamelena_Auth_Adapter_Bcrypt implements Zend_Auth_Adapter_Interface
{
    protected $_username;
    protected $_password;
    protected $_resultRow;

    public function __construct($username = null, $password = null)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * @param string $username
     * @return Gamelena_Auth_Adapter_Bcrypt
     */
    public function setIdentity($username)
    {
        $this->_username = $username;
        return $this;
    }

    /**
     * @param string $password
     * @return Gamelena_Auth_Adapter_Bcrypt
     */
    public function setCredential($password)
    {
        $this->_password = $password;
        return $this;
    }

    /**
     * Performs an authentication attempt
     *
     * @return Zend_Auth_Result
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     */
    public function authenticate()
    {
        $usersModel = new AclUsersModel();
        // Assume findUserName returns a rowset, take the first one
        $row = $usersModel->findUserName($this->_username)->current();

        if (!$row) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                array('User not found.')
            );
        }

        // 1. Check Bcrypt
        // password_verify handles checking if it's a valid bcrypt hash format too
        if (password_verify($this->_password, $row->password)) {
            // Success!
            // If the hash needs rehash (e.g. cost changed), update it
            if (password_needs_rehash($row->password, PASSWORD_BCRYPT)) {
                $newHash = password_hash($this->_password, PASSWORD_BCRYPT);
                
                // Use adapter update directly to avoid AclUsersModel::update() re-hashing the password
                $usersModel->getAdapter()->update(
                    'acl_users',
                    array('password' => $newHash),
                    $usersModel->getAdapter()->quoteInto('id = ?', $row->id)
                );
                $row->password = $newHash;
            }
            $this->_resultRow = $row;
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $row,
                array('Authentication successful.')
            );
        }

        // 2. Fallback: Check if it's a legacy MD5 hash
        // MD5 hashes are 32 chars hex. We also check if it matches the md5 of input
        if (md5($this->_password) === $row->password) {
            // It's a match on legacy MD5! Open the champagne and upgrade them.
            $newHash = password_hash($this->_password, PASSWORD_BCRYPT);
            
            // Use adapter update directly to avoid AclUsersModel::update() re-hashing the password
            // And avoid "Cannot refresh row as parent is missing" error
            $usersModel->getAdapter()->update(
                'acl_users',
                array('password' => $newHash),
                $usersModel->getAdapter()->quoteInto('id = ?', $row->id)
            );
            $row->password = $newHash;

            $this->_resultRow = $row;
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $row,
                array('Authentication successful (migrated from MD5).')
            );
        }

        // 3. Password mismatch
        return new Zend_Auth_Result(
            Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
            null,
            array('Invalid credentials.')
        );
    }

    /**
     * Returns the result row as a stdClass object
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return stdClass|boolean
     */
    public function getResultRowObject($returnColumns = null, $omitColumns = null)
    {
        if (!$this->_resultRow) {
            return false;
        }

        $returnObject = new stdClass();

        if (null !== $returnColumns) {
            $availableColumns = array_keys($this->_resultRow->toArray());
            foreach ((array) $returnColumns as $returnColumn) {
                if (in_array($returnColumn, $availableColumns)) {
                    $returnObject->{$returnColumn} = $this->_resultRow->{$returnColumn};
                }
            }
            return $returnObject;
        } elseif (null !== $omitColumns) {
            $omitColumns = (array) $omitColumns;
            foreach ($this->_resultRow->toArray() as $resultColumn => $resultValue) {
                if (!in_array($resultColumn, $omitColumns)) {
                    $returnObject->{$resultColumn} = $resultValue;
                }
            }
            return $returnObject;
        } else {
            foreach ($this->_resultRow->toArray() as $resultColumn => $resultValue) {
                $returnObject->{$resultColumn} = $resultValue;
            }
            return $returnObject;
        }
    }
}
