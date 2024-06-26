<?php
require_once("DBConnection.php");

// Fetch user details if ID is provided in the URL
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM `user_list` where user_id = '{$_GET['id']}'");
    foreach($qry->fetchArray() as $k => $v){
        $$k = $v; // Assign each fetched value to a variable with corresponding column name as variable name
    }
}
?>
<div class="container-fluid">
    <form action="" id="user-form">
        <input type="hidden" name="id" value="<?php echo isset($user_id) ? $user_id : '' ?>">
        <div class="form-group">
            <label for="fullname" class="control-label">Full Name</label>
            <input type="text" name="fullname" id="fullname" required class="form-control form-control-sm rounded-0" value="<?php echo isset($fullname) ? $fullname : '' ?>">
        </div>
        <div class="form-group">
            <label for="username" class="control-label">Username</label>
            <input type="text" name="username" id="username" required class="form-control form-control-sm rounded-0" value="<?php echo isset($username) ? $username : '' ?>">
        </div>
        <div class="form-group d-flex justify-content-end">
            <button class="btn btn-sm btn-primary rounded-0">Save</button>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('#user-form').submit(function(e){
            e.preventDefault(); // Prevent default form submission
            $('.pop_msg').remove(); // Remove any existing alert messages
            var _this = $(this);
            var _el = $('<div>'); // Create a new div element for displaying messages
            _el.addClass('pop_msg'); // Add a class to the message div
            $('#uni_modal button').attr('disabled',true); // Disable modal buttons
            $('#uni_modal button[type="submit"]').text('Submitting form...'); // Change modal submit button text

            // Perform AJAX request
            $.ajax({
                url:'./Actions.php?a=save_user',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
                error: function(err) {
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
                        if("<?php echo isset($user_id) ?>" != 1)
                            _this.get(0).reset(); // Reset form if not editing existing record
                    } else {
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
