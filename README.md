# Smartphone Application for Real Time Driving Behavior Analysis and Feedback
<h6>Recently working on it</h6>

<h3>Xampp setup queries</h3>
<h4>owner_details Table</h4>

  ```sql
  CREATE TABLE IF NOT EXISTS owner_details (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    car_model VARCHAR(255) NOT NULL,
    car_photo VARCHAR(255) NOT NULL,
    car_registration_number VARCHAR(50) UNIQUE NOT NULL,
    owner_photo VARCHAR(255) NOT NULL,
    owner_license_photos VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
<h4>driver_details Table</h4>

  ```sql
  CREATE TABLE IF NOT EXISTS driver_details (
    driver_id INT AUTO_INCREMENT PRIMARY KEY,  
    driver_name VARCHAR(255) NOT NULL,         
    email VARCHAR(255) UNIQUE NOT NULL,        
    phone VARCHAR(15) NOT NULL,                
    password VARCHAR(255) NOT NULL,            
    car_registration_number VARCHAR(50) NOT NULL, - Car registration number (foreign key)
    driver_photo VARCHAR(255) NOT NULL,        
    license_photo VARCHAR(255) NOT NULL,       
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (car_registration_number) REFERENCES cars(registration_number) ON DELETE CASCADE  -- Link to the cars table
);
```

<h4>cars Table</h4>

  ```sql
  CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_email VARCHAR(255) NOT NULL,          
    registration_number VARCHAR(50) UNIQUE NOT NULL, 
    car_model VARCHAR(255) NOT NULL,             
    car_image_path VARCHAR(255) NOT NULL,        
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (owner_email) REFERENCES owner_details(email) ON DELETE CASCADE  -- Foreign key to the owner's email
 );
```
