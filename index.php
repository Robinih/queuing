<?php
session_start();
// Redirect to login page if user is not authenticated
if(!isset($_SESSION['user_id'])){
    header("Location:./login.php");
    exit;
}
require_once('DBConnection.php');
$page = isset($_GET['page']) ? $_GET['page'] : 'home'; // Determine which page to load based on 'page' query parameter
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucwords(str_replace('_',' ',$page)) ?> | Cashier Queuing System</title>
    <!-- CSS -->
    <link rel="stylesheet" href="./Font-Awesome-master/css/all.min.css">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./select2/css/select2.min.css">
    <link rel="stylesheet" href="./DataTables/datatables.min.css">
    <style>
        /* Custom CSS styles */
        /* ... */
    </style>
    <!-- JavaScript -->
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./DataTables/datatables.min.js"></script>
    <script src="./Font-Awesome-master/js/all.min.js"></script>
    <script src="./select2/js/select2.min.js"></script>
    <script src="./js/script.js"></script>
</head>
<body>
    <main>
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient" id="topNavBar">
            <div class="container">
                <a class="navbar-brand" href="./">Queuing</a>
                <!-- Toggle button for collapsed navigation links -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <!-- Dynamic navigation links -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'home') ? 'active' : '' ?>" href="./">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'users') ? 'active' : '' ?>" href="./?page=users">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./?page=cashiers">Cashier List</a>
                        </li>
                    </ul>
                </div>
                <!-- User Dropdown Menu -->
                <div>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle bg-transparent text-light border-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            Hello <?php echo $_SESSION['fullname'] ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="./?page=manage_account">Manage Account</a></li>
                            <li><a class="dropdown-item" href="./Actions.php?a=logout">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content Container -->
        <div class="container py-3" id="page-container">
            <!-- Display flash message if set -->
            <?php if(isset($_SESSION['flashdata'])): ?>
            <div class="dynamic_alert alert alert-<?php echo $_SESSION['flashdata']['type'] ?>">
                <div class="float-end"><a href="javascript:void(0)" class="text-dark text-decoration-none" onclick="$(this).closest('.dynamic_alert').hide('slow').remove()">x</a></div>
                <?php echo $_SESSION['flashdata']['msg'] ?>
            </div>
            <?php unset($_SESSION['flashdata']) ?>
            <?php endif; ?>
            
            <!-- Include page content based on 'page' parameter -->
            <?php include $page.'.php'; ?>
        </div>
    </main>
    
    <!-- Modals for various purposes -->
    <!-- Universal Modal -->
    <div class="modal fade" id="uni_modal" role='dialog' data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm rounded-0 btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                    <button type="button" class="btn btn-sm rounded-0 btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Secondary Universal Modal -->
    <div class="modal fade" id="uni_modal_secondary" role='dialog' data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm rounded-0 btn-primary" id='submit' onclick="$('#uni_modal_secondary form').submit()">Save</button>
                    <button type="button" class="btn btn-sm rounded-0 btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content rounded-0">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Confirmation</h5>
                </div>
                <div class="modal-body">
                    <div id="delete_content"></div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-primary btn-sm rounded-0" id='confirm' onclick="">Continue</button>
                    <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>
