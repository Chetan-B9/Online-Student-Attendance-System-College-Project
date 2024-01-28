<?php
session_start();
include "../../../Components/Header.php";
include "../../../Components/MenuBox.php";
$connection = mysqli_connect('localhost', 'root', '', 'getattend');

$teacher_id = $_SESSION['teacher'];
$course_query = "SELECT Courses FROM teacher_courses WHERE Teacher_ID = '$teacher_id'";
$course_result = $connection->query($course_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="../../../css/bootstrap.css" />

  <!-- fonts style -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

  <!-- font awesome style -->
  <link href="../../../css/font-awesome.min.css" rel="stylesheet" />

  <!-- Custom styles for this template -->
  <link href="../../../css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="../../../css/responsive.css" rel="stylesheet" />
  <link href="../../../css/get attendance/AttendanceInfo2.css" rel="stylesheet" />

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

    #contentContainer {
      width: 100%;
      height: 91.2vh;
      display: grid;
      grid-template-columns: 20% 70% 10%;

    }

    #subContainer_1 {
      height: 96%;
      box-shadow: 5px 5px 5px #d9d9d9,
        -0.5px -0.5px 5px #ffffff;

    }

    #subContainer_2 {
      padding: 1rem 2rem;

    }


    #menu_name_container {
      margin: 0rem 0rem 0rem 1.25rem;
      padding: 1rem 0rem 1rem 0rem;
    }

    #menu_name_container h5 {
      font-weight: bolder;
      height: 2.5rem;
      margin-top: 0.8rem;
      text-align: center;

    }

    #menu_box_container {

      margin-top: 1rem;
      padding: 0rem 0rem 0rem 1.25rem;

    }
  </style>
</head>

<body>
  <?php
  $obj = new Header;
  $obj->Header("../../../Logo/Get Attend Logo ( Transparent ).png", "../../../Home.php", "../../../About.php");
  ?>
  <div id="contentContainer">
    <div id="subContainer_1">
      <div id="menu_name_container">
        <h3 id="menu_name">Get Attendance</h3>
      </div>

      <div id="menu_box_container">
        <?php
        $dashB = new MenuBox;
        $dashB->menubox("fa-solid fa-house", "Dashboard", "../dashboard.php");
        $dashB->menubox("fa-solid fa-users", "Get Attendance", "attendance_type.php",);
        $dashB->menubox("fa-solid fa-eye", "Veiw Attendance", "../View Attendance/teacherViewAttendance.php");
        ?>

      </div>

    </div>
    <div id="subContainer_2">
      <div id="main_container">

        <form id="info_container" method="POST" action="attendance_type.php">
          <!-- <input type="text" class="input_1" name="class" placeholder="Enter Class" /> -->
          <select class="input_1" name="class">
            <option value="Select Class">Select Class</option>
            <?php  
               while($course_row = mysqli_fetch_array($course_result)){
                ?>
                   <option value="<?php echo $course_row['Courses']?>"><?php echo $course_row['Courses']?></option>

                <?php
               }
            ?>
          </select>
          <input type="text" class="input_1" name="batch" placeholder="Enter Batch (Ex: 2023-24)" />
          <select class="input_1" name="year">
            <option value="Select Year">Select Year</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>

          </select>
          <!-- <input type="text" class="input_1" name="year" placeholder="Enter Year (Ex: 1, 2, 3)" /> -->
          <input type="text" name="subject" placeholder="Enter Subject" />
          <div class="button_box"><button type="submit">Generate Code </button></div>
        </form>


      </div>

    </div>
  </div>
</body>

</html>

<?php

$total_at = 3;

if ($_SERVER['REQUEST_METHOD'] === "POST") {

  $class = $_POST['class'];
  $batch = $_POST['batch'];
  $year = $_POST['year'];
  $subject = $_POST['subject'];

  $_SESSION['batch'] = $batch;
  $_SESSION['year'] = $year;
  $_SESSION['subject'] = $subject;
  $_SESSION['class'] = $class;



  // insert rows if not exit 
  $varification_query = "SELECT * FROM total_attendance_record";
  $result = $connection->query($varification_query);
  if (!$result) {
    die($connection->error);
  } else {

    $get_total = "SELECT Total_attendance FROM total_attendance_record WHERE Teacher_ID = '$teacher_id' && Class = '$class' && Batch = '$batch' && Std_year = $year && Subjects = '$subject'";

    $get_total_result = $connection->query($get_total);
    $get_row = $get_total_result->fetch_assoc();

    if (mysqli_num_rows($result) > 0) {
      $match = false;
      while ($row2 = mysqli_fetch_array($result)) {
        if ($row2['Teacher_ID'] === $teacher_id && $row2['Class'] === $class && $row2['Batch'] === $batch && $row2['Std_year'] === $year && $row2['Subjects'] === $subject) {


          $total_atten = $get_row['Total_attendance'];
          $total_atten = $total_atten + 1;

          $update_query = "UPDATE total_attendance_record SET Total_attendance = $total_atten WHERE Teacher_ID = '$teacher_id' && Class = '$class' && Batch = '$batch' && Std_year = $year && Subjects = '$subject'";

          $update_result = $connection->query($update_query);
          if (!$update_result) {
            die($connection->error);
          }
?>
          <script>
            window.location.href = "Pin/pincodegeneration.php";
          </script>
        <?php
          $match = true;
        } else {
          $match = false;
        }

        if ($match === true) {
          break;
        }
      }

      if ($match === false) {

        if ($total_atten === null) {
          $total_atten = 1;
        }
        $inser_query = "INSERT INTO total_attendance_record (Teacher_ID, Batch, Class, Std_year, Subjects, Total_attendance) VALUES ('$teacher_id','$batch', '$class', '$year', '$subject', '$total_atten')";

        $result2 = $connection->query($inser_query);
        if (!$result2) {
          die($connection->error);
        }
        ?>
        <script>
          window.location.href = "Pin/pincodegeneration.php";
        </script>
      <?php
      }
      // break;
    } else {
      if ($total_atten === null) {
        $total_atten = 1;
      }

      $inser_query = "INSERT INTO total_attendance_record (Teacher_ID, Batch, Class, Std_year, Subjects, Total_attendance) VALUES ('$teacher_id','$batch', '$class', '$year', '$subject', '$total_atten')";

      $result2 = $connection->query($inser_query);
      if (!$result2) {
        die($connection->error);
      }

      ?>
      <script>
        window.location.href = "Pin/pincodegeneration.php";
      </script>
<?php
    }
  }
}

?>