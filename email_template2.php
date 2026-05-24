<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Subscribing</title>
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
            text-align: center;
        }

        /* Logo */
        .logo img {
            max-width: 180px;
            height: auto;
        }

        /* Thank You Image */
        .thank-you-img img {
            max-width: 50%;
            height: auto;
        }

        /* Text */
        .text {
            margin-top: 20px;
        }

        /* Add responsive styles for mobile devices */
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="assets/images/logo-dark.webp" alt="Company Logo">
        </div>
        <div class="thank-you-img">
            <img src="assets/images/thumbnails/thank_you.png" alt="Thank You">
        </div>
        <div class="text">
            <p>We have received your subscription. Thank you for joining our community!</p>
            <p>We will keep you updated with the latest news and offers. If you have any questions or requests, feel free to reach out to us.</p>
            <p>Best regards,</p>
            <p>Your Company Name</p>
        </div>
    </div>
</body>
</html>
