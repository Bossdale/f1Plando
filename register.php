<?php
    include 'connect.php';    
    //require_once 'includes/header.php'; 
?>

<!-- Bootstrap CSS Link -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="text-center mb-4">
                <h2>StoreStock Registration</h2>
            </div>

            <div class="card p-4 shadow-sm">
                <form method="post">
                    <div class="form-group">
                        <label for="txtfirstname">First Name</label>
                        <input type="text" class="form-control" id="txtfirstname" name="txtfirstname" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="txtlastname">Last Name</label>
                        <input type="text" class="form-control" id="txtlastname" name="txtlastname" required>
                    </div>

                    <div class="form-group">
                        <label for="txtgender">Gender</label>
                        <select class="form-control" id="txtgender" name="txtgender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="txtusertype">User Type</label>
                        <select class="form-control" id="txtusertype" name="txtusertype" required>
                            <option value="">Select User Type</option>
                            <option value="student">Student</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="txtusername">Username</label>
                        <input type="text" class="form-control" id="txtusername" name="txtusername" required>
                    </div>

                    <div class="form-group">
                        <label for="txtpassword">Password</label>
                        <input type="password" class="form-control" id="txtpassword" name="txtpassword" required>
                    </div>

                    <div class="form-group">
                        <label for="txtprogram">Program</label>
                        <select class="form-control" id="txtprogram" name="txtprogram" required>
                            <option value="">Select Program</option>
                            <option value="bsit">BSIT</option>
                            <option value="bscs">BSCS</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="txtyearlevel">Year Level</label>
                        <select class="form-control" id="txtyearlevel" name="txtyearlevel" required>
                            <option value="">Select Year Level</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>

                    <button type="submit" name="btnRegister" class="btn btn-primary btn-block">Register</button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php   
    if(isset($_POST['btnRegister'])){        

        $fname=$_POST['txtfirstname'];        
        $lname=$_POST['txtlastname'];
        $gender=$_POST['txtgender'];
        $utype=$_POST['txtusertype'];
        $uname=$_POST['txtusername'];        
        $pword=$_POST['txtpassword'];    
        $hashedpw = password_hash($pword, PASSWORD_DEFAULT);
        

        $prog=$_POST['txtprogram'];        
        $yearlevel=$_POST['txtyearlevel'];        
        
    
        $sql1 ="Insert into tbluser(firstname,lastname,gender, usertype, username, password) 
            values('".$fname."','".$lname."','".$gender."','".$utype."', '".$uname."', '".$hashedpw."')";
        mysqli_query($connection,$sql1);
                
        $last_id = mysqli_insert_id($connection);
         
        $sql2 ="Insert into tblstudent(program, yearlevel, uid) values('".$prog."','".$yearlevel."','".$last_id."')";
        mysqli_query($connection,$sql2);

        echo "<script language='javascript'>
            alert('New record saved.');
        </script>";
        header("location: dashboard.php");
    }
?>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php include 'footer.php'; ?>
