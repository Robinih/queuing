<?php 
session_start();  // Start or resume the session
require_once('DBConnection.php');  // Include the database connection class file

/*****************************************************************************************************************************************/
/********************************************* Extend DBConnection class to inherit database functionality *******************************/
/*****************************************************************************************************************************************/

Class Actions extends DBConnection {
    
    function __construct() {
        parent::__construct();  // Call parent class constructor (DBConnection)
    }
    
    function __destruct() {
        parent::__destruct();  // Call parent class destructor (DBConnection)
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to handle user login
/*****************************************************************************************************************************************/

    function login() {
        extract($_POST);  // Extract POST data into variables
        
        // Construct SQL query to fetch user from database using username and hashed password
        $sql = "SELECT * FROM user_list WHERE username = '{$username}' AND `password` = '".md5($password)."' ";
        @$qry = $this->query($sql)->fetchArray();  // Execute query and fetch result as associative array
        
        if (!$qry) {
            // If no matching user found
            $resp['status'] = "failed";
            $resp['msg'] = "Invalid username or password.";
        } else {
            // If user found, set session variables and return success message
            $resp['status'] = "success";
            $resp['msg'] = "Login successfully.";
            foreach ($qry as $k => $v) {
                if (!is_numeric($k))  // Avoid numeric keys
                    $_SESSION[$k] = $v;  // Set session variable
            }
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/
    

/*****************************************************************************************************************************************/
    // Function to handle user logout
/*****************************************************************************************************************************************/

    function logout() {
        session_destroy();  // Destroy all session data
        header("location:./login.php");  // Redirect to login page
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to handle cashier login
/*****************************************************************************************************************************************/

    function c_login() {
        extract($_POST);  // Extract POST data into variables
        
        // Construct SQL query to fetch cashier from database using cashier_id
        $sql = "SELECT * FROM cashier_list WHERE cashier_id = '{$cashier_id}'";
        @$qry = $this->query($sql)->fetchArray();  // Execute query and fetch result as associative array
        
        if ($qry) {
            if ($qry['log_status'] == 0) {
                // If cashier is not logged in
                $resp['status'] = "success";
                $resp['msg'] = "Login successfully.";
                $this->query("UPDATE `cashier_list` SET log_status = 1 WHERE cashier_id = {$cashier_id}");
                foreach ($qry as $k => $v) {
                    if (!is_numeric($k))  // Avoid numeric keys
                        $_SESSION[$k] = $v;  // Set session variable
                }
            } else {
                // If cashier is already logged in
                $resp['status'] = "failed";
                $resp['msg'] = "Cashier is In-Use.";
            }
        } else {
            // If query fails
            $resp['status'] = "failed";
            $resp['msg'] = "An Error occured. Error: ".$this->lastErrorMsg();
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/
    

/*****************************************************************************************************************************************/
    // Function to handle cashier logout
/*****************************************************************************************************************************************/

    function c_logout() {
        session_destroy();  // Destroy all session data
        $this->query("UPDATE `cashier_list` SET log_status = 0 WHERE cashier_id = {$_SESSION['cashier_id']}");
        header("location:./cashier");  // Redirect to cashier page
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to save or update user details
/*****************************************************************************************************************************************/

    function save_user() {
        extract($_POST);  // Extract POST data into variables
        $data = "";  // Initialize $data variable
        
        // Iterate through POST data to construct SQL data
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id'))) {
                if (!empty($id)) {
                    if (!empty($data)) $data .= ",";
                    $data .= " `{$k}` = '{$v}' ";
                } else {
                    $cols[] = $k;
                    $values[] = "'{$v}'";
                }
            }
        }
        
        // If new user, add password to data array
        if (empty($id)) {
            $cols[] = 'password';
            $values[] = "'".md5($username)."'";
        }
        
        // If columns and values are set, construct SQL data string
        if (isset($cols) && isset($values)) {
            $data = "(".implode(',',$cols).") VALUES (".implode(',',$values).")";
        }
        
        // Check if username already exists
        @$check = $this->query("SELECT count(user_id) as `count` FROM user_list WHERE `username` = '{$username}' ".($id > 0 ? " AND user_id != '{$id}' " : ""))->fetchArray()['count'];
        
        if (@$check > 0) {
            // If username already exists
            $resp['status'] = 'failed';
            $resp['msg'] = "Username already exists.";
        } else {
            // If username does not exist, save user details
            if (empty($id)) {
                $sql = "INSERT INTO `user_list` {$data}";
            } else {
                $sql = "UPDATE `user_list` SET {$data} WHERE user_id = '{$id}'";
            }
            
            @$save = $this->query($sql);
            if ($save) {
                $resp['status'] = 'success';
                if (empty($id))
                    $resp['msg'] = 'New User successfully saved.';
                else
                    $resp['msg'] = 'User Details successfully updated.';
            } else {
                $resp['status'] = 'failed';
                $resp['msg'] = 'Saving User Details Failed. Error: '.$this->lastErrorMsg();
                $resp['sql'] = $sql;
            }
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to delete user
/*****************************************************************************************************************************************/
    function delete_user() {
        extract($_POST);  // Extract POST data into variables
        
        // Delete user from user_list table based on user_id
        @$delete = $this->query("DELETE FROM `user_list` WHERE rowid = '{$id}'");
        if ($delete) {
            // If delete operation successful
            $resp['status'] = 'success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'User successfully deleted.';
        } else {
            // If delete operation fails
            $resp['status'] = 'failed';
            $resp['error'] = $this->lastErrorMsg();
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to update user credentials
/*****************************************************************************************************************************************/

    function update_credentials() {
        extract($_POST);  // Extract POST data into variables
        $data = "";  // Initialize $data variable
        
        // Iterate through POST data to construct SQL data
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id','old_password')) && !empty($v)) {
                if (!empty($data)) $data .= ",";
                if ($k == 'password') $v = md5($v);
                $data .= " `{$k}` = '{$v}' ";
            }
        }
        
        // Check if old password matches current session password
        if (!empty($password) && md5($old_password) != $_SESSION['password']) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Old password is incorrect.";
        } else {
            // Update user credentials in user_list table
            $sql = "UPDATE `user_list` SET {$data} WHERE user_id = '{$_SESSION['user_id']}'";
            @$save = $this->query($sql);
            if ($save) {
                // If update successful, update session variables
                $resp['status'] = 'success';
                $_SESSION['flashdata']['type'] = 'success';
                $_SESSION['flashdata']['msg'] = 'Credential successfully updated.';
                foreach ($_POST as $k => $v) {
                    if (!in_array($k, array('id','old_password')) && !empty($v)) {
                        if (!empty($data)) $data .= ",";
                        if ($k == 'password') $v = md5($v);
                        $_SESSION[$k] = $v;
                    }
                }
            } else {
                // If update fails
                $resp['status'] = 'failed';
                $resp['msg'] = 'Updating Credentials Failed. Error: '.$this->lastErrorMsg();
                $resp['sql'] = $sql;
            }
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to save or update cashier details
/*****************************************************************************************************************************************/

    function save_cashier() {
        extract($_POST);  // Extract POST data into variables
        $data = "";  // Initialize $data variable
        
        // Iterate through POST data to construct SQL data
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id'))) {
                $v = addslashes(trim($v));
                if (empty($id)) {
                    $cols[] = "`{$k}`";
                    $vals[] = "'{$v}'";
                } else {
                    if (!empty($data)) $data .= ", ";
                    $data .= " `{$k}` = '{$v}' ";
                }
            }
        }
        
        // If columns and values are set, construct SQL query
        if (isset($cols) && isset($vals)) {
            $cols_join = implode(",", $cols);
            $vals_join = implode(",", $vals);
        }
        
        // Check if cashier name already exists
        @$check = $this->query("SELECT COUNT(cashier_id) as count FROM `cashier_list` WHERE `name` = '{$name}' ".($id > 0 ? " AND cashier_id != '{$id}'" : ""))->fetchArray()['count'];
        
        if (@$check > 0) {
            // If cashier name already exists
            $resp['status'] = 'failed';
            $resp['msg'] = 'Cashier already exists.';
        } else {
            // If cashier name does not exist, save or update cashier details
            @$save = $this->query($sql);
            if ($save) {
                $resp['status'] = "success";
                if (empty($id))
                    $resp['msg'] = "Cashier successfully saved.";
                else
                    $resp['msg'] = "Cashier successfully updated.";
            } else {
                $resp['status'] = "failed";
                if (empty($id))
                    $resp['msg'] = "Saving New Cashier Failed.";
                else
                    $resp['msg'] = "Updating Cashier Failed.";
                $resp['error'] = $this->lastErrorMsg();
            }
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to delete cashier
/*****************************************************************************************************************************************/

    function delete_cashier() {
        extract($_POST);  // Extract POST data into variables
        
        // Check if cashier is currently logged in
        $get = $this->query("SELECT * FROM `cashier_list` WHERE cashier_id = '{$id}'");
        @$res = $get->fetchArray();
        $is_logged = false;
        
        if ($res) {
            $is_logged = $res['log_status'] == 1 ? true : false;
            if ($is_logged) {
                // If cashier is logged in, cannot delete
                $resp['status'] = 'failed';
                $resp['msg'] = 'Cashier is in use.';
            } else {
                // If cashier is not logged in, delete from cashier_list table
                @$delete = $this->query("DELETE FROM `cashier_list` WHERE cashier_id = '{$id}'");
                if ($delete) {
                    // If delete operation successful
                    $resp['status'] = 'success';
                    $_SESSION['flashdata']['type'] = 'success';
                    $_SESSION['flashdata']['msg'] = 'Cashier successfully deleted.';
                } else {
                    // If delete operation fails
                    $resp['status'] = 'failed';
                    $resp['error'] = $this->lastErrorMsg();
                }
            }
        } else {
            // If query fails
            $resp['status'] = 'failed';
            $resp['error'] = $this->lastErrorMsg();
        }
        
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to save queue
/*****************************************************************************************************************************************/

    function save_queue() {
        $code = sprintf("%'.04d", 1);  // Format queue code with leading zeros
        
        // Loop to generate unique queue code for today's date
        while (true) {
            $chk = $this->query("SELECT count(queue_id) `count` FROM `queue_list` WHERE queue = '".$code."' AND date(date_created) = '".date('Y-m-d')."' ")->fetchArray()['count'];
            if ($chk > 0) {
                $code = sprintf("%'.04d", abs($code) + 1);
            } else {
                break;
            }
        }
        
        $_POST['queue'] = $code;  // Set queue code from generated code
        extract($_POST);  // Extract POST data into variables
        
        // Construct SQL query to insert queue into queue_list table
        $sql = "INSERT INTO `queue_list` (`queue`,`customer_name`) VALUES('{$queue}','{$customer_name}')";
        $save = $this->query($sql);  // Execute SQL query
        
        if ($save) {
            // If save operation successful
            $resp['status'] = 'success';
            $resp['id'] = $this->query("SELECT last_insert_rowid()")->fetchArray()[0];
        } else {
            // If save operation fails
            $resp['status'] = 'failed';
            $resp['msg'] = "An error occured. Error: ".$this->lastErrorMsg();
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to get queue details
/*****************************************************************************************************************************************/

    function get_queue() {
        extract($_POST);  // Extract POST data into variables
        
        // Fetch queue details from queue_list table based on queue_id
        $qry = $this->query("SELECT * FROM `queue_list` WHERE queue_id = '{$qid}' ");
        @$res = $qry->fetchArray();
        
        $resp['status'] = 'success';
        if ($res) {
            // If queue details found, set response variables
            $resp['queue'] = $res['queue'];
            $resp['name'] = $res['customer_name'];
        } else {
            // If no queue details found
            $resp['queue'] = "";
            $resp['name'] = "";
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to fetch next queue
/*****************************************************************************************************************************************/

    function next_queue() {
        extract($_POST);  // Extract POST data into variables
        
        // Fetch next available queue from queue_list table for today's date
        $get = $this->query("SELECT queue_id, `queue`, customer_name FROM `queue_list` WHERE status = 0 AND date(date_created) = '".date("Y-m-d")."' ORDER BY queue_id ASC LIMIT 1");
        @$res = $get->fetchArray();
        
        $resp['status'] = 'success';
        if ($res) {
            // If next queue found, update its status to '1' (processed)
            $this->query("UPDATE `queue_list` SET status = 1 WHERE queue_id = '{$res['queue_id']}'");
            $resp['data'] = $res;  // Set response data
        } else {
            $resp['data'] = $res;  // Set response data
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/


/*****************************************************************************************************************************************/
    // Function to update video file
/*****************************************************************************************************************************************/

    function update_video() {
        extract($_FILES);  // Extract FILE data into variables
        
        $mime = mime_content_type($vid['tmp_name']);  // Get MIME type of uploaded video
        
        if (strstr($mime, 'video/') > -1) {
            // If uploaded file is a video file
            $move = move_uploaded_file($vid['tmp_name'], "./video/".(time())."_{$vid['name']}");  // Move uploaded file to 'video' folder
            
            if ($move) {
                // If video upload successful, update session flashdata and delete old video file if exists
                $resp['status'] = 'success';
                $_SESSION['flashdata']['type'] = 'success';
                $_SESSION['flashdata']['msg'] = 'Video was successfully updated.';
                if (is_file('./video/'.$_POST['video'])) {
                    unlink('./video/'.$_POST['video']);
                }
            } else {
                // If video upload fails
                $resp['status'] = 'false';
                $resp['msg'] = 'Unable to upload the video.';
            }
        } else {
            // If uploaded file is not a valid video file
            $resp['status'] = 'false';
            $resp['msg'] = 'Invalid video type.';
        }
        return json_encode($resp);  // Return response as JSON
    }
/*****************************************************************************************************************************************/
}


/*****************************************************************************************************************************************/
/*****************************************************************************************************************************************/
/*****************************************************************************************************************************************/

$a = isset($_GET['a']) ? $_GET['a'] : '';  // Get 'a' parameter from URL query string
$action = new Actions();  // Create new instance of Actions class

switch ($a) {
    case 'login':
        echo $action->login();  // Call login method
        break;
    case 'c_login':
        echo $action->c_login();  // Call c_login method
        break;
    case 'logout':
        echo $action->logout();  // Call logout method
        break;
    case 'c_logout':
        echo $action->c_logout();  // Call c_logout method
        break;
    case 'save_user':
        echo $action->save_user();  // Call save_user method
        break;
    case 'delete_user':
        echo $action->delete_user();  // Call delete_user method
        break;
    case 'update_credentials':
        echo $action->update_credentials();  // Call update_credentials method
        break;
    case 'save_cashier':
        echo $action->save_cashier();  // Call save_cashier method
        break;
    case 'delete_cashier':
        echo $action->delete_cashier();  // Call delete_cashier method
        break;
    case 'save_queue':
        echo $action->save_queue();  // Call save_queue method
        break;
    case 'get_queue':
        echo $action->get_queue();  // Call get_queue method
        break;
    case 'next_queue':
        echo $action->next_queue();  // Call next_queue method
        break;
    case 'update_video':
        echo $action->update_video();  // Call update_video method
        break;
    default:
        // Default action here
        break;
}
