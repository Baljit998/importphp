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
    // Check for file upload errors
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        die("File upload error: " . $_FILES['file']['error']);
    }

    // File upload handling
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");

    // Skip the first row (header) of the CSV file
    fgetcsv($handle, 1000, ",");

    // Loop through the remaining rows and insert data into the database
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $admission_no = trim($data[0]);
        $names = trim($data[1]);
        $class = trim($data[2]);
        $date_of_birth = trim($data[3]);
        $father_name = trim($data[4]);
        $mother_name = trim($data[5]);
        $contact_no = trim($data[6]);
        $address = trim($data[7]);

        // Insert data into the table using prepared statement
        $sql = "INSERT INTO student (admission_no, names, class, date_of_birth, father_name, mother_name, contact_no, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        // Use prepared statement to avoid SQL injection
        $stmt = $conn->prepare($sql);

        // Check if the prepare was successful
        if ($stmt) {
            $stmt->bind_param("ssssssss", $admission_no, $names, $class, $date_of_birth, $father_name, $mother_name, $contact_no, $address);

            if ($stmt->execute()) {
                echo "Record inserted successfully!<br>";
            } else {
                echo "Error executing statement: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    fclose($handle);
    echo "CSV data imported successfully!";
}

// Close the database connection
$conn->close();
?>
