<?php
require_once("DBConnection.php");

// Fetch cashier details if ID is provided in the URL
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM `cashier_list` where cashier_id = '{$_GET['id']}'");
    foreach($qry->fetchArray() as $k => $v){
        $$k = $v; // Assign each fetched value to a variable with corresponding column name as variable name
    }
}
?>
<div class="container-fluid">
    <form action="" id="cashier-form">
        <input type="hidden" name="id" value="<?php echo isset($cashier_id) ? $cashier_id : '' ?>">
        <div class="form-group">
            <label for="name" class="control-label">Name</label>
            <input type="text" name="name" autofocus id="name" required class="form-control form-control-sm rounded-0" value="<?php echo isset($name) ? $name : '' ?>">
        </div>
        <div class="form-group">
            <label for="status" class="control-label">Status</label>
            <select name="status" id="status" class="form-select form-select-sm rounded-0" required>
                <option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group d-flex justify-content-end">
            <button class="btn btn-sm btn-primary rounded-0">Save</button>
        </div>
    </form>
</div>

<script>
    $(function(){
        $('#cashier-form').submit(function(e){
            e.preventDefault(); // Prevent default form submission
            $('.pop_msg').remove(); // Remove any existing alert messages
            var _this = $(this);
            var _el = $('<div>'); // Create a new div element for displaying messages
            _el.addClass('pop_msg'); // Add a class to the message div
            $('#uni_modal button').attr('disabled',true); // Disable modal buttons
            $('#uni_modal button[type="submit"]').text('Submitting form...'); // Change modal submit button text

            // Perform AJAX request
            $.ajax({
                url:'./Actions.php?a=save_cashier',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
                error:err=>{
                    console.log(err);
                    _el.addClass('alert alert-danger');
                    _el.text("An error occurred.");
                    _this.prepend(_el);
                    _el.show('slow');
                    $('#uni_modal button').attr('disabled',false);
                    $('#uni_modal button[type="submit"]').text('Save');
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        _el.addClass('alert alert-success');
                        $('#uni_modal').on('hide.bs.modal',function(){
                            location.reload(); // Reload the page on modal close
                        });
                        if("<?php echo isset($cashier_id) ?>" != 1)
                            _this.get(0).reset(); // Reset form if not editing existing record
                    }else{
                        _el.addClass('alert alert-danger');
                    }
                    _el.text(resp.msg);

                    _el.hide();
                    _this.prepend(_el);
                    _el.show('slow');
                    $('#uni_modal button').attr('disabled',false);
                    $('#uni_modal button[type="submit"]').text('Save');
                }
            });
        });
    });
</script>
