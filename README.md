##  Government Hospital Resource Allocation System

##  About the Project
The Government Hospital Resource Allocation System is a web-based application developed to manage patient registration, hospital bed allocation, patient admission, discharge, and hospital resource monitoring.

It contains a dashboard, patient records, bed management, allocation history, and reports to help hospital staff manage resources efficiently.

##  Features
- **Dashboard:** View total patients, total beds, available beds, occupied beds, admitted patients, waiting patients, and discharged patients.
- **Register Patient:** Enter patient ID, name, age, and health issue.
- **Patient Records:** View patient details, priority, bed number, admission date, and discharge date.
- **Bed Management:** Monitor 10 normal beds and 3 emergency buffer beds.
- **Priority-Based Allocation:** Allocate beds based on availability and patient priority.
- **Patient Discharge:** Discharge patients and release their beds.
- **Allocation History:** View patient allocation and discharge status.
- **Reports:** View patient statistics and health issue-wise and date-wise reports.

##  Technologies Used
- HTML
- CSS
- JavaScript
- PHP
- MySQL / MariaDB
- XAMPP
- Git and GitHub

##  Installation and Setup

### Step 1: Install XAMPP
Download and install XAMPP on your computer.

### Step 2: Download the Project
Download this repository as a ZIP file from GitHub and extract it.

Alternatively, clone the repository using Git:

bash
git clone https://github.com/harshinis25cse-creator/HospitalProject.git


### Step 3: Copy the Project Folder
Copy the extracted `HospitalProject` folder into:

text
C:\xampp\htdocs\


### Step 4: Start the Server
Open the XAMPP Control Panel and start:
- Apache
- MySQL

### Step 5: Configure the Database
1. Open `http://localhost/phpmyadmin/`.
2. Create a database named `hospital_db`.
3. Create or import the required patient table and database structure.
4. Open `db.php` and configure the database connection.

**Note:** The database structure must be available before running the application. Do not upload database passwords or other private credentials to GitHub.

### Step 6: Run the Application
Open your browser and visit:

text
http://localhost/HospitalProject/


##  How to Use the Application

### 1. Dashboard
Open Dashboard to view patient statistics and hospital bed availability.

### 2. Register Patient
Click **Register Patient** and enter:
- Patient ID
- Patient Name
- Age
- Health Issue

Click **Register Patient** to save the details.

### 3. Patient Records
Open **Patient Records** to view registered patients, their priority, bed number, admission date, and current status.

### 4. Bed Management
Open **Bed Management** to check which beds are available or occupied.

- Normal Beds: 1–10
- Emergency Buffer Beds: 101–103
- Green: Available
- Red: Occupied

### 5. Discharge Patient
In Patient Records, click **Discharge** for an admitted patient. The patient's status changes to discharged, and the allocated bed becomes available.

### 6. Allocation History
Open **Allocation History** to view patient allocation and discharge records.

### 7. Reports
Open **Reports** to view:
- Total registered patients
- Currently admitted patients
- Waiting patients
- Discharged patients
- Health issue-wise statistics
- Date-wise reports

##  Project Objective
To improve hospital resource utilization, simplify patient management, and support efficient bed allocation through a centralized web application.

##  Future Enhancements
- Secure login for hospital staff
- Electronic medical records
- Printable reports
- Real-time notifications
- Advanced patient monitoring

##  Conclusion
The Government Hospital Resource Allocation System provides a simple way to manage patient records, monitor bed availability, allocate hospital beds, and maintain admission and discharge information.

**Project Type:** Academic Mini Project
