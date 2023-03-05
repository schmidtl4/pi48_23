<?php

    // see https://www.wikihow.com/Create-a-Secure-Session-Management-System-in-PHP-and-MySQL

    /**
     *  TO USE ON PAGE:
     *      include this on every page you want to access the sessions
     *      use it instead of session_start();
     *   require('session.class.php'); //not needed if autoloader used?
     *
     *   $session = new SessionManager();
     *   // Set to true if using https
     *   $session->start_session('_s', false);
     */

    class SessionManager {

        //CONSTRUCT
        //private mysqli $db;

        function __construct() {

            $httpHost = $_SERVER['HTTP_HOST'];
            $user = SECURE_USER;
            $pass = SECURE_PW;
            $database = 'pineisla_secure_sessions_dev';
            if ($httpHost == 'rev23.pineisland48.com') {
                $database = 'pineisla_secure_sessions';
            }

            if (!empty(MYSQL_PORT)) {
                $dsn = 'mysql:host='.MYSQL_HOST.'; port='.MYSQL_PORT.'; dbname='.$database.'; charset=utf8mb4';
            } else {
                $dsn = 'mysql:host='.MYSQL_HOST.'; dbname='.$this->dbn.'; charset=utf8mb4';
            }

            try {
                $link = new PDO($dsn, SECURE_USER, SECURE_PW, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | SECURE_USER', SECURE_USER);
                dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | SECURE_PW', SECURE_PW);

            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
            //require '../vendor/stefangabos/zebra_session/Zebra_Session.php';
            if (is_numeric(DEFAULT_TIMEOUT)) {
                new Zebra_Session($link, 'e7Jg7Fb9GWS9', DEFAULT_TIMEOUT);
            } else {
                new Zebra_Session(
                    $link,
                    'e7Jg7Fb9GWS9');
            }

            //dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | state of $_SESSION before instantiating Zebra_Session',
            //    $_SESSION);

        }


        //OPEN DATABASE
        /*function open()
        {
            //$host = '184.175.68.18:3306';
            $host = 'localhost';
            $user = SECURE_USER;
            $pass = SECURE_PW;
            //$name = 'lesschmi_secure_sessions';
            $name = 'secure_sessions';
            $mysqli = new mysqli($host, $user, $pass, $name);
            $this->db = $mysqli;
                return true;
            }*/


        //CLOSE DATABASE
        function close() {
            $this->db->close();
                return true;
        }


        //READ SESSION FROM DB
        function read($id)
        {
            if (!isset($this->read_stmt)) {
                $this->read_stmt = $this->db->prepare('SELECT session_data FROM session_data WHERE session_id = ? LIMIT 1');
            }
            $this->read_stmt->bind_param('s', $id);
            $this->read_stmt->execute();
            $this->read_stmt->store_result();
            $this->read_stmt->bind_result($data);
            $this->read_stmt->fetch();
            $key = $this->getkey($id);
            echo '<br>id: '.$id;
            echo '<br>key: '.$key;
            echo '<br>data: '.$data;
            echo '<br>decrypted: '.$this->decrypt($data, $key);
            return $this->decrypt($data, $key);
        }


        //WRITE SESSION TO DB
        function write($id, $data)
        {
            // Get unique key
            $key = $this->getkey($id);
            // Encrypt the data
            $data = $this->encrypt($data, $key);

            if (!isset($this->w_stmt)) {
                $this->w_stmt = $this->db->prepare('REPLACE INTO session_data (session_id, session_data, hash) VALUES (?, ?, ?)');
            }

            $this->w_stmt->bind_param('iss', $id, $data, $key);
            $this->w_stmt->execute();
            return TRUE;
        }


        //SESSION DESTROY
        function destroy($id)
        {
            if (!isset($this->delete_stmt)) {
                $this->delete_stmt = $this->db->prepare('DELETE FROM session_data WHERE session_id = ?');
            }
            $this->delete_stmt->bind_param('s', $id);
            $this->delete_stmt->execute();
            return TRUE;
        }


        //GARBAGE COLLECTION
        function gc($max)
        {
            if (!isset($this->gc_stmt)) {
                $this->gc_stmt = $this->db->prepare('DELETE FROM session_data WHERE session_expire < ?');
            }
            $old = time() - $max;
            $this->gc_stmt->bind_param('s', $old);
            $this->gc_stmt->execute();
            return TRUE;
        }


        //GET KEY
        private function getkey($id)
        {
            if (!isset($this->key_stmt)) {
                $this->key_stmt = $this->db->prepare('SELECT hash FROM session_data WHERE session_id = ? LIMIT 1');
            }
            $this->key_stmt->bind_param('s', $id);
            $this->key_stmt->execute();
            $this->key_stmt->store_result();
            if ($this->key_stmt->num_rows == 1) {
                $this->key_stmt->bind_result($key);
                $this->key_stmt->fetch();
                return $key;
            } else {
                return hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), TRUE));

            }
        }


        //ENCRYPT
        /**
         * wiki.how used deprecated functions
         * replaced with this function from https://www.delftstack.com/howto/php/php-aes-encrypt-decrypt/
         *
         * @param $data
         * @param $key
         *
         * @return false|string
         */
        private function encrypt($data, $key) {

            echo '<br>encrypting... key: '.$key;
            echo '<br>data: '.$data;

            $cipher_algo = 'AES-128-CTR'; //The cipher method, in our case, AES
            $iv_length = openssl_cipher_iv_length($cipher_algo); //The length of the initialization vector
            $option = 0; //Bitwise disjunction of flags
            $encrypt_iv = 'H!CTLjSq64NkEvpM'; //Initialization vector, non-null
            $encrypt_key = $key; // The encryption key
            // Use openssl_encrypt() encrypt the given string
            return openssl_encrypt($data, $cipher_algo, $encrypt_key, $option, $encrypt_iv);
        }


        //DECRYPT
        /**
         * wiki.how used deprecated functions
         * replaced with this function from https://www.delftstack.com/howto/php/php-aes-encrypt-decrypt/
         *
         * @param $data
         * @param $key
         *
         * @return false|string
         */
        private function decrypt($data, $key)
        {
            echo '<br>decrypting... key: '.$key;
            echo '<br>data: '.$data;

            $decrypt_iv = 'H!CTLjSq64NkEvpM'; //Initialization vector, non-null
            $decrypt_key = $key; // The encryption key
            $cipher_algo = 'AES-128-CTR';
            $option = 0;
            // Use openssl_decrypt() to decrypt the string
            return openssl_decrypt($data, $cipher_algo, $decrypt_key, $option, $decrypt_iv);
        }


        //IS STARTED?
        public function sessionStarted() {
            if(session_id() == '') {
                return false;
            } else {
                return true;
            }
        }


        //SESSON EXISTS?
        public function sessionExists($session) {
            /*if($this->sessionStarted() == false) {
                session_start();
            }*/
            if(isset($_SESSION[$session])) {
                return true;
            } else {
                return false;
            }
        }
    }
