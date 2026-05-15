<?php

class DatabaseConnection
{
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_password = "";
    private $db_name = "ecommerce_store";

    public function openConnection()
    {
        $connection = new mysqli(
            $this->db_host,
            $this->db_user,
            $this->db_password,
            $this->db_name
        );

        if ($connection->connect_error) {
            die("Database connection failed: " . $connection->connect_error);
        }

        $connection->set_charset("utf8mb4");

        return $connection;
    }

    public function closeConnection($connection)
    {
        if ($connection) {
            $connection->close();
        }
    }

}

?>
