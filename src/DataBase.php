<?php
    namespace Milantex\DAW;

    /**
     * The DataBase interface wrapper class
     */
    final class DataBase {
        /**
         * The PDO object for the open connection
         * @var PDO
         */
        private $connection;

        /**
         * Stores the error recorded after the last execute method was ran
         * @var array
         */
        private $lastExecutionError = NULL;

        /**
         * Stores the number of rows affected by the last execute method
         * @var int
         */
        private $lastAffectedRowCount = 0;

        /**
         * The DataBase class constructor function
         * @param string $dbHost
         * @param string $dbName
         * @param string $dbUser
         * @param string $dbPass
         * @return \PDO
         */
        public function __construct(string $dbHost, string $dbName, string $dbUser, string $dbPass) {
            try {
                $this->connection = new \PDO('mysql:hostname=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
            } catch (Exception $e) {
                // TODO: Deal with this later
            }
        }

        /**
         * Performs a SELECT SQL query with given parameters for the selected
         * connection and can be made to fetch a single result or all results.
         * @param string $sql
         * @param array $parameters
         * @param bool $return_one
         * @return NULL|stdClass|array
         */
        private function select($sql, $parameters = [], $return_one = TRUE) {
            if (!$this->connection) {
                return ($return_one == TRUE) ? NULL : [];
            }

            $prep = $this->connection->prepare($sql);
            if (!$prep) {
                return ($return_one == TRUE) ? NULL : [];
            }

            $res = $prep->execute($parameters);
            if (!$res) {
                return ($return_one == TRUE) ? NULL : [];
            }

            if ($return_one == TRUE) {
                return $prep->fetch(\PDO::FETCH_OBJ);
            } else {
                return $prep->fetchAll(\PDO::FETCH_OBJ);
            }
        }

        /**
         * Executes the select method of this class specifying the expectation
         * of a single record return value.
         * @param string $sql
         * @param array $parameters
         * @return NULL|stdClass
         */
        public function selectOne($sql, $parameters = []) {
            return $this->select($sql, $parameters, TRUE);
        }

        /**
         * Executes the select method of this class specifying the expectation
         * of an array of records being returned.
         * @param string $sql
         * @param array $parameters
         * @return NULL|stdClass
         */
        public function selectMany($sql, $parameters = []) {
            return $this->select($sql, $parameters, FALSE);
        }

        /**
         * Performs an execution of an SQL statement for the selected connection
         * and returns the result of the execution for processing to the caller.
         * @param string $sql
         * @param array $parameters
         * @param string $connection
         * @return type
         */
        public function execute($sql, $parameters = []) {
            if (!$this->connection) {
                return NULL;
            }

            $prep = $this->connection->prepare($sql);
            if (!$prep) {
                return NULL;
            }

            $res = $prep->execute($parameters);
            if (!$res) {
                $this->lastExecutionError = $prep->errorInfo();
                $this->lastAffectedRowCount = NULL;
            } else {
                $this->lastExecutionError = NULL;
                $this->lastAffectedRowCount = $prep->rowCount();
            }

            return $res;
        }

        /**
         * Returns the error recorded after the last execute method failure.
         * One error can be retrieved once. After it is returned, it is reset.
         * @return array|NULL
         */
        public function getLastExecutionError() {
            if (!isset($this->lastExecutionError)) {
                $this->lastExecutionError = NULL;
            }

            $error = $this->lastExecutionError;

            $this->lastExecutionError = NULL;

            return $error;
        }

        /**
         * Returns the affected row count after the last execute method success.
         * This method returns NULL if there was an error or if the execute
         * method was never ran. It can return 0 if no rows were affected.
         * @return int|NULL
         */
        public function getLastExecutionAffectedRownCount() {
            if (!isset($this->lastAffectedRowCount)) {
                $this->lastAffectedRowCount = NULL;
            }

            $error = $this->lastAffectedRowCount;

            $this->lastAffectedRowCount = NULL;

            return $error;
        }

        /**
         * Returns the last insert ID after INSERT on this connection
         * @return int
         */
        public function getLastInsertId() {
            return $this->connection->lastInsertId();
        }
    }
