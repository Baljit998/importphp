<?php
// Database connection parameters
$host = 'localhost:3310';
$username = 'root';
$password = '';
$database = 'school';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // File upload handling
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");

    // Loop through the CSV file and insert data into the database
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $admission_no = $data[0];
        $names = $data[1];
        $class = $data[2];
        $date_of_birth = $data[3];
        $father_name = $data[4];
        $mother_name = $data[5];
        $contact_no = $data[6];
        $address = $data[7];

        // Insert data into the table
        $sql = "INSERT INTO student (admission_no, names, class, date_of_birth, father_name, mother_name, contact_no, address) 
                VALUES ('$admission_no', '$names', '$class', '$date_of_birth', '$father_name', '$mother_name', '$contact_no', '$address')";
        $conn->query($sql);
    }

    fclose($handle);
    echo "CSV data imported successfully!";
}

// Close the database connection
$conn->close();
?>
