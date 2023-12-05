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

    $notInsertedRows = array();

    // Loop through the remaining rows and insert data into the database
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $admission_no = trim($data[0]);  // Assuming admission_no is the first column
        $names = trim($data[1]);
        $class = trim($data[2]);
        $date_of_birth = trim($data[3]);
        $father_name = trim($data[4]);
        $mother_name = trim($data[5]);
        $contact_no = trim($data[6]);
        $address = trim($data[7]);
        $school_id = trim($data[8]);

        // Check if admission number and class are not empty
        if (!empty($admission_no) && !empty($class)) {
            // Check if a record with the same admission number already exists
            $check_sql = "SELECT admission_no FROM student_data WHERE admission_no = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $admission_no);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                echo "Record with admission number $admission_no already exists. Skipping insertion.<br>";
            } else {
                // Insert data into the table using prepared statement
                $insert_sql = "INSERT INTO student_data (admission_no, names, class, date_of_birth, father_name, mother_name, contact_no, address, school_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                // Use prepared statement to avoid SQL injection
                $insert_stmt = $conn->prepare($insert_sql);

                // Check if the prepare was successful
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sssssssss", $admission_no, $names, $class, $date_of_birth, $father_name, $mother_name, $contact_no, $address, $school_id);

                    if ($insert_stmt->execute()) {
                        echo "Record inserted successfully!<br>";
                    } else {
                        echo "Error executing statement: " . $insert_stmt->error;
                        $notInsertedRows[] = $data; // Store the row that was not inserted
                    }

                    $insert_stmt->close();
                } else {
                    echo "Error preparing statement for insertion: " . $conn->error;
                    $notInsertedRows[] = $data; // Store the row that was not inserted
                }
            }

            $check_stmt->close();
        } else {
            echo "Admission number or class is empty. Skipping insertion for row.<br>";
            $notInsertedRows[] = $data; // Store the row that was not inserted
        }
    }

    fclose($handle);

    echo "CSV data imported successfully!<br>";

    // Print the rows that were not inserted
    if (!empty($notInsertedRows)) {
        echo "<br>Rows not inserted:<br>";
        foreach ($notInsertedRows as $row) {
            echo implode(', ', $row) . "<br>";
        }
    }
}

// Close the database connection
$conn->close();
?>
