<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Cashier List</h3>
        <div class="card-tools align-middle">
            <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="create_new">Add New</button>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped table-bordered">
            <colgroup>
                <col width="5%">
                <col width="30%">
                <col width="25%">
                <col width="25%">
                <col width="15%">
            </colgroup>
            <thead>
                <tr>
                    <th class="text-center p-0">#</th>
                    <th class="text-center p-0">Name</th>
                    <th class="text-center p-0">Log Status</th>
                    <th class="text-center p-0">Status</th>
                    <th class="text-center p-0">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
/**********************************************************************************************************/
                // Query to fetch cashier data from the database
/**********************************************************************************************************/

                $sql = "SELECT * FROM `cashier_list`  ORDER BY `name` ASC";
                $qry = $conn->query($sql);
                $i = 1;
                while($row = $qry->fetchArray()):
                ?>
                <tr>
                    <td class="text-center p-0"><?php echo $i++; ?></td>
                    <td class="py-0 px-1"><?php echo $row['name'] ?></td>
                    <td class="py-0 px-1 text-center">
                        <?php 
/**********************************************************************************************************/
                        // Display Log Status with badge based on database value
/**********************************************************************************************************/

                        if($row['log_status'] == 1){
                            echo  '<span class="py-1 px-3 badge rounded-pill bg-success"><small>In-Use</small></span>';
                        }else{
                            echo  '<span class="py-1 px-3 badge rounded-pill bg-danger"><small>Not In-Use</small></span>';
                        }
                        ?>
                    </td>
                    <td class="py-0 px-1 text-center">
                        <?php 
/**********************************************************************************************************/
                        // Display Status with badge based on database value
/**********************************************************************************************************/

                        if($row['status'] == 1){
                            echo  '<span class="py-1 px-3 badge rounded-pill bg-success"><small>Active</small></span>';
                        }else{
                            echo  '<span class="py-1 px-3 badge rounded-pill bg-danger"><small>In-Active</small></span>';
                        }
                        ?>
                    </td>
                    <th class="text-center py-0 px-1">
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle btn-sm rounded-0 py-0" data-bs-toggle="dropdown" aria-expanded="false">
                                Action
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <!-- Edit and Delete actions with data attributes for identification -->
                                <li><a class="dropdown-item edit_data" data-id='<?php echo $row['cashier_id'] ?>' href="javascript:void(0)">Edit</a></li>
                                <li><a class="dropdown-item delete_data" data-id='<?php echo $row['cashier_id'] ?>' data-name='<?php echo $row['name'] ?>' href="javascript:void(0)">Delete</a></li>
                            </ul>
                        </div>
                    </th>
                </tr>
                <?php endwhile; ?>
                <?php if($qry->numRows() == 0): ?>
                <!-- Display message if no data is found -->
                <tr>
                    <td class="text-center p-0" colspan="5">No data display.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    
    $(function(){
        // Add New button click event handler
        $('#create_new').click(function(){
            // Open a modal to add a new cashier record
            uni_modal('Add New Cashier',"manage_cashier.php")
        });

        // Edit button click event handler for each row
        $('.edit_data').click(function(){
            // Open a modal to edit cashier details for the specific cashier_id
            uni_modal('Edit Cashier Details',"manage_cashier.php?id="+$(this).attr('data-id'))
        });

        // Delete button click event handler for each row
        $('.delete_data').click(function(){
            // Confirm deletion and trigger delete action
            _conf("Are you sure to delete <b>"+$(this).attr('data-name')+"</b> from list?",'delete_data',[$(this).attr('data-id')])
        });
    });
/**********************************************************************************************************/
    // Function to handle deletion of cashier record
/**********************************************************************************************************/

    function delete_data(id){
        // Disable button to prevent multiple clicks
        $('#confirm_modal button').attr('disabled',true)
        $.ajax({
            url:'./Actions.php?a=delete_cashier',
            method:'POST',
            data:{id:id},
            dataType:'JSON',
            error:function(err){
                console.log(err)
                alert("An error occurred.")
                // Re-enable the button on error
                $('#confirm_modal button').attr('disabled',false)
            },
            success:function(resp){
                if(resp.status == 'success'){
                    // Reload the page after successful deletion
                    location.reload()
                }else if(resp.status == 'failed' && !!resp.msg){
                    // Display error message in modal body
                    var el = $('<div>')
                    el.addClass('alert alert-danger pop-msg')
                    el.text(resp.msg)
                    el.hide()
                    $('#confirm_modal .modal-body').prepend(el)
                    el.show('slow')
                }else{
                    // Alert in case of unexpected error
                    alert("An error occurred.")
                }
                // Re-enable the button after processing
                $('#confirm_modal button').attr('disabled',false)
            }
        })
    }
</script>
