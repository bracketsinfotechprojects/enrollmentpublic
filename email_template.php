<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
    <style>
        /* Reset some default email client styles */
        body, table, td, th {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Container */
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Top Line */
        .top-line {
            border-top: 3px solid #007BFF; /* Unique style for the top line */
            margin-top: 10px;
        }

        /* Logo */
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 150px; /* Adjust the max-width to control the logo size */
            height: auto;
        }

        /* List */
        .data-list {
            list-style: none;
            padding: 0;
        }

        .data-list-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f2f2f2;
        }

        /* Add responsive styles for mobile devices */
        @media (max-width: 480px) {
            .data-list-item {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">        
        <div class="logo">
            <img src="assets/images/logo-dark.webp" alt="Company Logo">
        </div>
        <p style="text-align: center; font-weight: bold;">
            We have received a new Enquiry with the Enquiry ID
        </p>
        <div class="top-line"></div>
        <ul class="data-list">
            <li class="data-list-item">
                <strong>Name:</strong> John Doe
            </li>
            <li class="data-list-item">
                <strong>Email:</strong> john@example.com
            </li>
            <li class="data-list-item">
                <strong>Phone Number:</strong> (123) 456-7890
            </li>
            <li class="data-list-item">
                <strong>Location:</strong> New York
            </li>
            <li class="data-list-item">
                <strong>Qualification:</strong> PhD
            </li>
            <!-- Add more list items as needed -->
        </ul>
    </div>
</body>
</html>
