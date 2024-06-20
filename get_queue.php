<?php
// Include the DBConnection.php file which likely contains the database connection setup
require_once('./DBConnection.php');

// Check if 'id' parameter is set in the GET request
if(isset($_GET['id'])){
    // Query the `queue_list` table for the record with queue_id equal to the 'id' parameter
    $qry = $conn->query("SELECT * FROM `queue_list` where queue_id = '{$_GET['id']}'");

    // Fetch the result as an associative array
    @$res = $qry->fetchArray();

    // If result is found, iterate through the array and assign values to variables
    if($res){
        foreach($res as $k => $v){
            if(!is_numeric($k)){
                $$k = $v; // Dynamically assign values to variables named after keys
            }
        }
    }
}
?>
<style>
    #uni_modal .modal-footer{
        display:none; /* CSS to hide the modal footer of #uni_modal */
    }
</style>
<div class="container fluid">
    <?php if(isset($_GET['success']) && $_GET['success'] == true): ?>
        <div class="alert alert-success">Your Queue Number is successfully generated.</div>
    <?php endif; ?>
    <div id="outprint">
        <div class="row justify-content-end">
            <div class="col-12">
                <div class="card border-0 border-left border-start rounded-0 border-5 border-info">
                    <div class="fs-1 fw-bold text-center"><?php echo $queue ?></div>
                    <center><?php echo $customer_name ?></center>
                </div>
            </div>
        </div>
    </div>
    <div class="row my-2 mx-0 justify-content-end align-items-center">
        <button class="btn btn-success rounded-0 me-2 col-sm-4" id="print" type="button"><i class="fa fa-print"></i> Print</button>
        <button class="btn btn-dark rounded-0 col-sm-4" data-bs-dismiss="modal" type="button"><i class="fa fa-times"></i> Close</button>
    </div>
</div>
<script>
    $(function(){
        $('#print').click(function(){
            var _el = $('<div>')
            var _h = $('head').clone()
            var _p = $('#outprint').clone()
            _h.find('title').text("Queue Number - Print")
            _el.append(_h)
            _el.append(_p)
            var nw = window.open('','_blank','width=700,height=500,top=75,left=200')
            nw.document.write(_el.html())
            nw.document.close()
            setTimeout(() => {
                nw.print()
                setTimeout(() => {
                    nw.close()
                    $('#uni_modal').modal('hide')
                }, 200);
            }, 500);
        })
    })
</script>