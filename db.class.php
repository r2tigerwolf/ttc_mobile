<?php    
    class Connect {
        private $db_host = 'localhost';
        private $db_user = 'root';
        private $db_password = '';
        private $db_db = 'ttc';
    
        function dbconnect(){           
            $conn = mysqli_connect($this->db_host, $this->db_user, $this->db_password, $this->db_db);
            return $conn;
        }
    } 
   
    class Bus {
        private $result;

        public function select($conn, $row, $table, $join, $where, $sort, $limit) {
            if($where) {
                $where = ' WHERE ' . $where;
            }
            
            $sql = 'SELECT ' . $row . ' FROM `' . $table . '` ' . $join . $where . ' ' . $sort . ' ' . $limit;
        
            $result =  $conn->query($sql);
            $keyResult = $conn->query($sql);

            if($result->num_rows) {
                $busInfo = array_keys($keyResult->fetch_assoc());
                $i = 0;
                
                while ($bus = $result->fetch_assoc()) {
                    for($j = 0; $j < count($busInfo); $j++) {
                        $this->result[$i][$busInfo[$j]] = $bus[$busInfo[$j]];
                    }
                    $i++;
                }
                
                mysqli_close($conn);
                return $this->result; 
            }
            else {
                mysqli_close($conn);
                return false; 
            }
        }
    }
?>