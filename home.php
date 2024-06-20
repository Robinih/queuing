<h3>Welcome to Cashier Queuing System</h3>
<hr>
<div class="col-12">
    <div class="col-md-12">
        <?php 
            // Scan the './video' directory to get the list of files
            $vid = scandir('./video');
            // Select the first video file found (assuming the directory has only one video file)
            $video = $vid[2];
            // Check if a video file exists
            if($video):
        ?>
            <!-- Display the video player -->
            <center><video src="./video/<?php echo $video ?>" autoplay muted controls id="vid_loop" class="bg-dark" loop style="height:50vh;width:75%"></video></center>
        <?php 
            endif; 
        ?>
        <!-- Video update form -->
        <form action="" id="upload-form">
            <!-- Hidden input to store current video file name -->
            <input type="hidden" name="video" value="<?php echo $video; ?>">
            <div class="row justify-content-center my-2">
                <div class="form-group col-md-4">
                    <label for="vid" class="control-label">Update Video</label>
                    <!-- Input field to select a new video file -->
                    <input type="file" name="vid" id="vid" class="form-control" accept="video/*" required>
                </div>
            </div>
            <div class="row justify-content-center my-2">
                <center>
                    <!-- Button to submit the form and update the video -->
                    <button class="btn btn-primary" type="submit">Update</button>
                </center>
            </div>
        </form>
    </div>
</div>
<script>
    $(function(){
        // jQuery function to handle form submission
        $('#upload-form').submit(function(e){
            e.preventDefault(); // Prevent default form submission
            $('.pop_msg').remove(); // Remove any existing error/success messages
            var _this = $(this); // Reference to the form element
            var _el = $('<div>'); // Create a new div element to display messages
            _el.addClass('pop_msg'); // Add a class to the message div

            _this.find('button').attr('disabled',true); // Disable the submit button
            _this.find('button[type="submit"]').text('updating video...'); // Change button text to indicate processing

            // AJAX request to update the video file
            $.ajax({
                url:'./Actions.php?a=update_video', // URL to handle the update action
                data: new FormData($(this)[0]), // Form data including the new video file
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: function(err){
                    console.log(err); // Log any errors to the console
                    _el.addClass('alert alert-danger'); // Add classes for error styling
                    _el.text("An error occurred."); // Set error message text
                    _this.prepend(_el); // Prepend error message to the form
                    _el.show('slow'); // Show error message with slow animation
                    _this.find('button').attr('disabled',false); // Enable the submit button
                    _this.find('button[type="submit"]').text('Update'); // Restore original button text
                },
                success: function(resp){
                    if(resp.status == 'success'){
                        _el.addClass('alert alert-success'); // Add classes for success styling
                        location.reload(); // Reload the page on success
                        if("<?php echo isset($department_id) ?>" != 1)
                            _this.get(0).reset(); // Reset the form if a condition is met
                    } else {
                        _el.addClass('alert alert-danger'); // Add classes for error styling
                    }
                    _el.text(resp.msg); // Set response message text

                    _el.hide(); // Hide message initially
                    _this.prepend(_el); // Prepend message to the form
                    _el.show('slow'); // Show message with slow animation
                    _this.find('button').attr('disabled',false); // Enable the submit button
                    _this.find('button[type="submit"]').text('Save'); // Restore original button text
                }
            });
        });
    });
</script>
