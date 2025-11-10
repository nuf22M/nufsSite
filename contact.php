<?php
    session_start();
    
    // CONFIGURATION - GAFE Contact Form
    $your_email = 'contact@globalacademy.lk';
    $errors = '';
    $success = '';
    $name = $email = $phone = $message = '';

    // Security function to detect malicious patterns
    function containsMaliciousContent($input) {
        $dangerous_patterns = [
            '/(<script|<iframe|<object|<embed|javascript:)/i',
            '/(content-type:|bcc:|cc:|to:)/i',
            '/(mime-version|content-transfer-encoding)/i',
            '/(%0A|%0D|\\n|\\r)/i',
            '/(eval\(|base64_decode|exec\(|system\()/i',
            '/(union.*select|insert.*into|delete.*from|drop.*table)/i',
            '/(<\?php|<\?=)/i',
            '/(\.\.\/|\.\.\\\\)/i'
        ];
        
        foreach($dangerous_patterns as $pattern) {
            if(preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    if(isset($_POST['submit-form']))
    {
        // Sanitize inputs
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['number']) ? trim($_POST['number']) : ''; // Note: field name is 'number' in contact.php
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Remove null bytes
        $name = str_replace(chr(0), '', $name);
        $email = str_replace(chr(0), '', $email);
        $phone = str_replace(chr(0), '', $phone);
        $message = str_replace(chr(0), '', $message);
        
        // Basic validation
        if(empty($name) || empty($email)) {
            $errors = "Name and Email are required fields.";    
        }
        
        // Phone validation (if provided)
        if(!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
            $errors = "Phone number must be exactly 10 digits.";
        }
        
        // Email injection protection - Enhanced
        if(!empty($email) && preg_match("/(\n+|\r+|\t+|%0A+|%0D+|%08+|%09+)/i", $email)) {
            $errors = "Invalid email address detected.";
        }
        
        // Additional email header injection check
        if(empty($errors)) {
            $suspicious_headers = ['content-type:', 'bcc:', 'cc:', 'to:', 'mime-version:', 'content-transfer-encoding:'];
            foreach($suspicious_headers as $header) {
                if(stripos($email, $header) !== false || stripos($name, $header) !== false) {
                    $errors = "Suspicious content detected. Please check your input.";
                    break;
                }
            }
        }
        
        // Check for malicious patterns in all fields
        if(empty($errors)) {
            if(containsMaliciousContent($name) || containsMaliciousContent($email) || 
               containsMaliciousContent($phone) || containsMaliciousContent($message)) {
                $errors = "Invalid content detected. Please remove special characters or code.";
            }
        }
        
        // CAPTCHA validation
        if(empty($errors)) {
            if(!isset($_POST['captcha']) || empty($_POST['captcha'])) {
                $errors = "Please enter the CAPTCHA code.";
            } elseif(!isset($_SESSION['captcha_code']) || strtolower($_POST['captcha']) !== strtolower($_SESSION['captcha_code'])) {
                $errors = "Invalid CAPTCHA code. Please try again.";
            }
        }
        
        if(empty($errors)) {
            // Send email with HTML template
            // Sanitize subject line - prevent header injection
            $clean_name = preg_replace('/[^\w\s-]/u', '', $name);
            $email_subject = "New Contact Form Submission from " . $clean_name;
            $email_subject = substr($email_subject, 0, 78);
            
            // HTML Email Template
            $email_body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
                    .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .email-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); padding: 30px 20px; text-align: center; }
                    .email-header h1 { margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; }
                    .email-content { padding: 30px; }
                    .info-box { background-color: #f9f9f9; border-left: 4px solid #3498db; padding: 15px; margin-bottom: 20px; }
                    .info-label { font-weight: 600; color: #333333; font-size: 14px; margin-bottom: 5px; }
                    .info-value { color: #555555; font-size: 15px; word-wrap: break-word; }
                    .message-box { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
                    .message-label { font-weight: 600; color: #333333; font-size: 14px; margin-bottom: 10px; }
                    .message-text { color: #555555; font-size: 15px; line-height: 1.6; white-space: pre-wrap; }
                    .email-footer { background-color: #333333; padding: 20px; text-align: center; color: #ffffff; font-size: 12px; }
                    .divider { border-bottom: 1px solid #e0e0e0; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <div class="email-header">
                        <h1>🎓 GAFE Contact Form</h1>
                    </div>
                    
                    <div class="email-content">
                        <p style="color: #333333; font-size: 16px; margin-bottom: 20px;">You have received a new contact form submission from your website.</p>
                        
                        <div class="info-box">
                            <div class="info-label">👤 Name:</div>
                            <div class="info-value">' . htmlspecialchars($name) . '</div>
                        </div>
                        
                        <div class="info-box">
                            <div class="info-label">📧 Email:</div>
                            <div class="info-value"><a href="mailto:' . htmlspecialchars($email) . '" style="color: #3498db; text-decoration: none;">' . htmlspecialchars($email) . '</a></div>
                        </div>
                        
                        <div class="info-box">
                            <div class="info-label">📞 Phone:</div>
                            <div class="info-value"><a href="tel:' . htmlspecialchars($phone) . '" style="color: #3498db; text-decoration: none;">' . htmlspecialchars($phone) . '</a></div>
                        </div>
                        
                        <div class="divider"></div>
                        
                        <div class="message-box">
                            <div class="message-label">💬 Message:</div>
                            <div class="message-text">' . htmlspecialchars($message) . '</div>
                        </div>
                    </div>
                    
                    <div class="email-footer">
                        <p style="margin: 0;">This email was sent from your GAFE website contact form.</p>
                        <p style="margin: 5px 0 0 0;">🌐 <a href="https://globalacademy.lk" style="color: #3498db; text-decoration: none;">globalacademy.lk</a></p>
                    </div>
                </div>
            </body>
            </html>
            ';
            
            // Sanitize email for headers to prevent injection
            $safe_email = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $your_email . "\r\n";
            $headers .= "Reply-To: " . $safe_email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            
            // Use ini_set to configure mail settings for better compatibility
            ini_set('SMTP', 'localhost');
            ini_set('smtp_port', '25');
            ini_set('sendmail_from', $your_email);
            
            // Alternative: Try different mail configurations
            $mail_sent = false;
            
            // Method 1: Standard mail() function
            if (!$mail_sent) {
                $mail_sent = @mail($your_email, $email_subject, $email_body, $headers);
            }
            
            // Method 2: Try with additional headers for better compatibility
            if (!$mail_sent) {
                $headers_alt = "MIME-Version: 1.0\r\n";
                $headers_alt .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers_alt .= "From: GAFE Contact Form <" . $your_email . ">\r\n";
                $headers_alt .= "Reply-To: " . $safe_email . "\r\n";
                $headers_alt .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                $headers_alt .= "X-Priority: 3\r\n";
                
                $mail_sent = @mail($your_email, $email_subject, $email_body, $headers_alt);
            }
            
            // Method 3: Try sending to multiple recipients (some hosts require this)
            if (!$mail_sent) {
                $additional_recipients = "info@globalacademy.lk"; // Backup email
                $mail_sent = @mail($your_email . ", " . $additional_recipients, $email_subject, $email_body, $headers);
            }
            
            if ($mail_sent) {
                $success = true;
                $_SESSION['message'] = "Your message has been sent successfully! We'll get back to you soon.";
                // Clear form values after successful submission
                $name = $email = $phone = $message = '';
            } else {
                // Fallback: Log the contact form submission to a file
                $log_entry = date('Y-m-d H:i:s') . " - Contact Form Submission\n";
                $log_entry .= "Name: " . $name . "\n";
                $log_entry .= "Email: " . $email . "\n";
                $log_entry .= "Phone: " . $phone . "\n";
                $log_entry .= "Message: " . $message . "\n";
                $log_entry .= "---\n\n";
                
                if (file_put_contents('contact_submissions.log', $log_entry, FILE_APPEND | LOCK_EX)) {
                    $success = true;
                    $_SESSION['message'] = "Your message has been received! We'll get back to you soon. (Note: Email service temporarily unavailable, but your message was saved.)";
                } else {
                    $errors = "Failed to send message. Please try again later or contact us directly at info@globalacademy.lk";
                }
            }
        }
    }
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta http-equiv="X-UA-Compatible" content="IE=edge">

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

<!-- Primary Meta Tags -->

<title>GAFE: Trusted Study Abroad Consultants – Global Education Experts</title>

<meta name="title" content="GAFE - Trusted Study Abroad Consultants" />

<meta name="description" content="Expert study abroad consultants offering Free guidance on student visas, scholarships, and programs at top universities worldwide. " />



<!-- Open Graph / Facebook -->

<meta property="og:type" content="website" />

<meta property="og:url" content="https://globalacademy.lk/" />

<meta property="og:title" content="GAFE - Trusted Study Abroad Consultants" />

<meta property="og:description" content="Expert study abroad consultants offering Free guidance on student visas, scholarships, and programs at top universities worldwide. " />

<meta property="og:image" content="https://metatags.io/images/meta-tags.png" />



<!-- Twitter -->

<meta property="twitter:card" content="summary_large_image" />

<meta property="twitter:url" content="https://globalacademy.lk/" />

<meta property="twitter:title" content="GAFE - Trusted Study Abroad Consultants" />

<meta property="twitter:description" content="Expert study abroad consultants offering Free guidance on student visas, scholarships, and programs at top universities worldwide. " />

<meta property="twitter:image" content="https://metatags.io/images/meta-tags.png" />



<!-- Meta Tags Generated with https://metatags.io -->





<!-- Fav Icon -->

<link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">



<!-- Google Fonts -->

<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">



<!-- Stylesheets -->

<link href="assets/css/font-awesome-all.css" rel="stylesheet">

<link href="assets/css/flaticon.css" rel="stylesheet">

<link href="assets/css/owl.css" rel="stylesheet">

<link href="assets/css/bootstrap.css" rel="stylesheet">

<link href="assets/css/jquery.fancybox.min.css" rel="stylesheet">

<link href="assets/css/animate.css" rel="stylesheet">

<link href="assets/css/color.css" rel="stylesheet">

<link href="assets/css/global.css" rel="stylesheet">

<link href="assets/css/elpath.css" rel="stylesheet">

<link href="assets/css/style.css" rel="stylesheet">

<link href="assets/css/responsive.css" rel="stylesheet">

<!-- Google tag (gtag.js) -->

<script async src="https://www.googletagmanager.com/gtag/js?id=G-8QT3K2HWVB">

</script>

<script>

  window.dataLayer = window.dataLayer || [];

  function gtag(){dataLayer.push(arguments);}

  gtag('js', new Date());



  gtag('config', 'G-8QT3K2HWVB');

</script>

</head>





<!-- page wrapper -->

<!-- <body>



    <div class="boxed_wrapper">


 -->


        <!-- preloader -->
<!-- 
        <div class="loader-wrap">

            <div class="preloader">

                <div class="preloader-close">x</div>

                <div id="handle-preloader" class="handle-preloader">

                    <div class="animation-preloader">

                        <div class="spinner"></div>

                        <div class="txt-loading">

                            <span data-text-preloader="G" class="letters-loading">

                                G

                            </span>

                            <span data-text-preloader="A" class="letters-loading">

                                A

                            </span>

                            <span data-text-preloader="F" class="letters-loading">

                                F

                            </span>

                            <span data-text-preloader="E" class="letters-loading">

                                E

                            </span>

                        </div>

                    </div>  

                </div>

            </div>

        </div> -->

        <!-- preloader end -->





        <!-- main header -->

        <header class="main-header">

            <div class="auto-container">

                <!-- header-top -->

                <div class="header-top">

                    <div class="top-inner clearfix">

                        <div class="left-column pull-left">

                            <ul class="info-list clearfix">

                                <li><i class="fas fa-phone-square"></i><a href="tel:+94722005787">+94 72 200 5787</a></li>

                                <li><i class="fas fa-envelope"></i><a href="mailto:info@globalacademy.lk">info@globalacademy.lk</a></li>

                            </ul>

                        </div>

                        <div class="right-column pull-right">

                            <ul class="social-links clearfix">

                                <li><a href="https://twitter.com/globalacademysl?s=21&fbclid=IwAR0-M9DxR__73pD2O60MHyIum13IQEbwy3i-jgsGPgQ1iiSRAB8OAE0qvM0"><i class="fab fab fa-twitter"></i></a></li>

                                <li><a href="https://www.facebook.com/globalacademysl"><i class="fab fab fa-facebook-f"></i></a></li>

                                <li><a href="https://wa.me/qr/5ENSNEJ5YO4EA1"><i class="fab fab fa-dribbble"></i></a></li>

                                <li><a href="https://www.instagram.com/gafesl"><i class="fab fab fa-instagram"></i></a></li>

                            </ul>

                            <div class="btn-box"><a href="contact.php">Book Appointment</a></div>

                        </div>

                    </div>

                </div>

                <!-- header-lower -->

                <div class="header-lower">

                    <div class="outer-box clearfix">

                        <div class="logo-box">

                            <figure class="logo"><a href="index.html"><img src="assets/images/logo.png" alt=""></a></figure>

                        </div>

                        <div class="menu-area clearfix">

                            <!--Mobile Navigation Toggler-->

                            <div class="mobile-nav-toggler">

                                <i class="icon-bar"></i>

                                <i class="icon-bar"></i>

                                <i class="icon-bar"></i>

                            </div>

                            <nav class="main-menu navbar-expand-md navbar-light">

                                <div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">

                                    <ul class="navigation clearfix">

                                        <li class="dropdown"><a href="index.html">Home</a>

                                        </li>

                                        <li class="dropdown"><a href="about.html">About Us</a>

                                            <ul>

                                                <li><a href="about.html">About Us</a></li>

                                                <li><a href="team.html">Our Team</a></li>

                                                <li><a href="team-details.html">Managing Director</a></li>

                                               

                                            </ul>

                                        </li>

                                        <li class="dropdown"><a href="index.html">Coaching</a>

                                            <ul>

                                                <li><a href="coaching.html">Coaching</a></li>

                                                <li><a href="coaching-details.html">USW English Placement Test</a></li>

                                                <li><a href="coaching-details-4.html">PTE Traning</a></li>

                                                <li><a href="coaching-details-3.html">Take IELTS</a></li>

                                                <li><a href="coaching-details-2.html">USW Kira Training Session</a></li>

                            

                                            </ul>

                                        </li> 

                                        <li class="dropdown"><a href="service.html">Services</a>

                                            <ul>

                                                <li><a href="service.html">Our Services</a></li>

                                                

                                                <li><a href="service-details-2.html">Students Visa</a></li>

                                                

                                                <li><a href="service-details-4.html">Dependent Visa</a></li>

                                                <li><a href="service-details-5.html">Visit Visa</a></li>

                                                <li><a href="service-details-6.html">UK Graduate visa</a></li>

                                                <li><a href="service-details-7.html">Student Nursing Visa</a></li>

                                                <li><a href="service-details-8.html">Other Services</a></li>

                                                

                                            </ul>

                                        </li>

                                        <li class="dropdown"><a href="country.html">Country</a>

                                            <ul>

                                                <li><a href="country.html">Country</a></li>

                                                

                                                <li><a href="united-kingdom.html">United Kingdom</a></li>

                                                <li><a href="australia.html">Australia</a></li>

                                                <li><a href="canada.html">Canada</a></li>

                                                <li><a href="belarus.html">Belarus</a></li>

                                                <li><a href="new-zealand.html">New Zealand</a></li>

                                                <li><a href="usa.html">USA</a></li>

                                               

                                            </ul>

                                        </li> 

                                       

                                        <li class="current"><a href="contact.php">Contact</a></li> 

                                        <li><a href="graduate.html">Graduate students</a></li>

                                    </ul>

                                </div>

                            </nav>

                            <div class="nav-right clearfix">

                                <div class="search-box-outer">

                                    <div class="dropdown">

                                        <button class="search-box-btn" type="button" id="dropdownMenu3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="flaticon-magnifying-glass"></i></button>

                                        <div class="dropdown-menu search-panel" aria-labelledby="dropdownMenu3">

                                            <div class="form-container">

                                                <form method="post" action="www.globalacademy.lk">

                                                    <div class="form-group">

                                                        <input type="search" name="search-field" value="" placeholder="Search...." required="">

                                                        <button type="submit" class="search-btn"><span class="fas fa-search"></span></button>

                                                    </div>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!--sticky Header-->

            <div class="sticky-header">

                <div class="auto-container">

                    <div class="outer-box clearfix">

                        <div class="logo-box pull-left">

                            <figure class="logo"><a href="index.html"><img src="assets/images/logo.png" alt=""></a></figure>

                        </div>

                        <div class="menu-area clearfix pull-right">

                            <nav class="main-menu clearfix">

                                <!--Keep This Empty / Menu will come through Javascript-->

                            </nav>

                            <div class="nav-right clearfix">

                                <div class="search-box-outer">

                                    <div class="dropdown">

                                        <button class="search-box-btn" type="button" id="dropdownMenu4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="flaticon-magnifying-glass"></i></button>

                                        <div class="dropdown-menu search-panel" aria-labelledby="dropdownMenu4">

                                            <div class="form-container">

                                                <form method="post" action="www.globalacademy.lk">

                                                    <div class="form-group">

                                                        <input type="search" name="search-field" value="" placeholder="Search...." required="">

                                                        <button type="submit" class="search-btn"><span class="fas fa-search"></span></button>

                                                    </div>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </header>

        <!-- main-header end -->



        <!-- Mobile Menu  -->

        <div class="mobile-menu">

            <div class="menu-backdrop"></div>

            <div class="close-btn"><i class="fas fa-times"></i></div>

            

            <nav class="menu-box">

                <div class="nav-logo"><a href="index.html"><img src="assets/images/logo-2.png" alt="" title=""></a></div>

                <div class="menu-outer"><!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header--></div>

                <div class="contact-info">

                    <h4>Contact Info</h4>

                    <ul>

                        <li>No.02 Girton School Road, Nugegoda Sri Lanka</li>

                        <li><a href="tel:+94722005787">+94 72 200 5787</a></li>

                        <li><a href="mailto:info@globalacademy.lk">info@globalacademy.lk</a></li>

                    </ul>

                </div>

                <div class="social-links">

                    <ul class="clearfix">

                        <li><a href="https://twitter.com/globalacademysl?s=21&fbclid=IwAR0-M9DxR__73pD2O60MHyIum13IQEbwy3i-jgsGPgQ1iiSRAB8OAE0qvM0"><span class="fab fa-twitter"></span></a></li>

                        <li><a href="https://www.facebook.com/globalacademysl"><span class="fab fa-facebook-square"></span></a></li>

                        <li><a href="index.html"><span class="fab fa-pinterest-p"></span></a></li>

                        <li><a href="https://www.instagram.com/gafesl"><span class="fab fa-instagram"></span></a></li>

                        <li><a href="www.youtube.com/@studyabroad5595"><span class="fab fa-youtube"></span></a></li>

                    </ul>

                </div>

            </nav>

        </div><!-- End Mobile Menu -->





        <!-- Page Title -->

        <section class="page-title p_relative" style="background-image: url(assets/images/background/page-title.jpg);">

            <div class="auto-container">

                <div class="content-box p_relative pt_170 pb_170">

                    <h1 class="d_block fs_40 lh_50 color_white fw_exbold color-white">Contact</h1>

                    <ul class="bread-crumb p_absolute r_0 b_0 d_iblock pl_30 pr_30 bg-white clearfix pt_4 pb_4">

                        <li class="p_relative d_iblock fs_12 lh_25 color_white fw_sbold pr_13 mr_5"><a href="index.html" class="color_white hov-color">Home</a></li>

                        <li class="p_relative d_iblock fs_12 lh_25 color_white fw_sbold">Contact</li>

                    </ul>

                </div>

            </div>

        </section>

        <!-- End Page Title -->





        <!-- contact-section -->

        <section class="contact-section p_relative pt_120 pb_120">

            <div class="auto-container">

                <div class="row clearfix">

                    <div class="col-lg-8 col-md-12 col-sm-12 content-column">

                    <?php
                        if (isset($_SESSION['message'])) {
                            echo '<div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745;">' . $_SESSION['message'] . '</div>';
                            unset($_SESSION['message']);  // Clear the message after it's displayed
                        }
                        
                        if (!empty($errors)) {
                            echo '<div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545;">' . $errors . '</div>';
                        }
                    ?>

                        <div class="content-box">

                            <div class="sec-title p_relative d_block mb_40">
                                

                                <span class="p_relative d_block fs_14 lh_20 fw_sbold theme-color mb_7">how we can help</span>

                                <h2 class="p_relative d_block lh_55 fw_exbold">Write a Message</h2>

                            </div>

                            <div class="form-inner">

                                <form method="post" action="" id="contact-form" class="default-form" onsubmit="return validateForm()">

                                    <div class="row clearfix">

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group mb_20">

                                            <input type="text" id="namex" name="name" placeholder="Your Name" required="">

                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group mb_20">

                                            <input type="text" id="emailx" name="email" placeholder="Email" required="">

                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group mb_20">

                                            <input type="text" id="numberx" name="number" placeholder="Phone Number" required="">

                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group mb_20">

                                            <textarea name="message" id="messagex" placeholder="Leave A Comment"></textarea>

                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group mb_20">

                                            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">

                                                <div style="flex: 1; min-width: 200px;">

                                                    <input type="text" name="captcha" placeholder="Enter CAPTCHA code" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">

                                                </div>

                                                <div style="flex-shrink: 0;">

                                                    <img src="captcha.php" alt="CAPTCHA" style="border: 1px solid #ddd; border-radius: 5px; cursor: pointer;" onclick="this.src='captcha.php?'+Math.random()" title="Click to refresh">

                                                </div>

                                            </div>

                                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Click the image to refresh if you can't read the code</small>

                                        </div>

                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group message-btn mr-0">

                                            <button class="theme-btn btn-one" type="submit" name="submit-form"><span>Send A Message</span></button>

                                        </div>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-4 col-md-12 col-sm-12 info-column">

                        <div class="info-inner p_relative d_block b_radius_10">

                            <div class="support-box p_relative d_block theme-color-bg pl_100 pt_40 pr_30 pb_40">

                                <div class="icon-box p_absolute l_50 t_50 fs_35 color-white"><i class="flaticon-phone-call"></i></div>

                                <span class="d_block fs_14 lh_20 fw_medium color-white">Call now</span>

                                <h5 class="d_block fs_18 lh_30 fw_medium color-white"><a href="tel:+94722005787" class="d_iblock color-white">+94 72 200 5787</a></h5>

                                <h5 class="d_block fs_18 lh_30 fw_medium color-white"><a href="tel:+94112812263" class="d_iblock color-white">+94 11 281 2263</a></h5>

                                <h5 class="d_block fs_18 lh_30 fw_medium color-white"><a href="tel:+94112864442" class="d_iblock color-white">+94 11 286 4442</a></h5>

                            </div>

                            <div class="inner p_relative d_block pt_45 pr_30 pb_50 pl_50">

                                <ul class="info-list clearfix p_relative d_block mb_30">

                                    <li><a href="info@globalacademy.lk">info@globalacademy.lk</a></li>

                                    <li>No.02 Girton School Road, Nugegoda Sri Lanka</li>

                                    <li>No.56/3 Main St, Battaramulla Sri Lanka</li>

                                </ul>

                                <ul class="social-links clearfix">

                                    <li><a href="https://www.facebook.com/globalacademysl"><i class="fab fa-facebook-f"></i></a></li>

                                    <li><a href="https://twitter.com/globalacademysl?s=21&fbclid=IwAR0-M9DxR__73pD2O60MHyIum13IQEbwy3i-jgsGPgQ1iiSRAB8OAE0qvM0"><i class="fab fa-twitter"></i></a></li>

                                    <li><a href="https://www.instagram.com/gafesl"><i class="fab fa-instagram"></i></a></li>

                                    <li><a href="https://wa.me/qr/5ENSNEJ5YO4EA1"><i class="fab fa-dribbble"></i></a></li>

                                </ul>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- contact-section end -->

<div class="map2">

    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.1635225993!2d79.88476577456242!3d6.87100099312768!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25a66ffffffff%3A0x55f44085d6cd5f21!2sGAFE%20Consultants!5e0!3m2!1sen!2slk!4v1695804343028!5m2!1sen!2slk" width="712" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.9024672296!2d79.91118287456275!3d6.902266393097071!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae259879b41a531%3A0xc073d8c0e2f57efe!2sGAFE%20consultants%20-%20Study%20Abroad%20Consultants%20-%20Sri%20Lanka!5e0!3m2!1sen!2slk!4v1695805242121!5m2!1sen!2slk" width="712" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

</div>



        <!-- google-map-section -->

        <!-- <section class="google-map-section p_relative">

            <div class="map-inner">

                <div 

                    class="google-map" 

                    id="contact-google-map" 

                    data-map-lat="40.712776" 

                    data-map-lng="-74.005974" 

                    data-icon-path="assets/images/icons/map-marker.png"  

                    data-map-title="Brooklyn, New York, United Kingdom" 

                    data-map-zoom="12" 

                    data-markers='{

                        "marker-1": [40.712776, -74.005974, "<h4>Branch Office</h4><p>77/99 New York</p>","assets/images/icons/map-marker.png"]

                    }'>



                </div>

            </div>

        </section> -->

        <!-- google-map-section -->





        <!-- main-footer -->

        <section class="main-footer">

            <div class="image-layer p_absolute r_0 b_0" style="background-image: url(assets/images/shape/shape-5.png);"></div>

            <div class="footer-top">

                <div class="auto-container">

                    <div class="top-inner">

                        <div class="info-box">

                            <div class="icon-box"><i class="flaticon-message"></i></div>

                            <span>Email address</span>

                            <h5><a href="mailto:info@globalacademy.lk">info@globalacademy.lk</a></h5>

                        </div>

                        <figure class="footer-logo"><a href="index.html"><img src="assets/images/logo-2.png" alt=""></a></figure>

                        <div class="info-box">

                            <div class="icon-box"><i class="flaticon-phone-call"></i></div>

                            <span>Call now</span>

                            <h5><a href="tel:+94722005787">+94 72 200 5787</a></h5>

                        </div>

                    </div>

                </div>

            </div>

            <div class="widget-section">

                <div class="auto-container">

                    <div class="row clearfix">

                        <div class="col-lg-3 col-md-6 col-sm-12 footer-column">

                            <div class="footer-widget about-widget">

                                <div class="widget-title">

                                    <h4>About</h4>

                                </div>

                                <div class="widget-content">

                                    <p>Unlock Global Opportunities: Your Passport to Study Abroad Success with Expert Student Consulting Services.</p>

                                    <ul class="social-links clearfix">

                                        <li><a href="https://twitter.com/globalacademysl?s=21&fbclid=IwAR0-M9DxR__73pD2O60MHyIum13IQEbwy3i-jgsGPgQ1iiSRAB8OAE0qvM0"><i class="fab fa-twitter"></i></a></li>

                                        <li><a href="https://www.facebook.com/globalacademysl"><i class="fab fa-facebook-f"></i></a></li>

                                        <li><a href="index.html"><i class="fab fa-pinterest-p"></i></a></li>

                                        <li><a href="https://www.instagram.com/gafesl"><i class="fab fa-instagram"></i></a></li>

                                    </ul>

                                </div>

                            </div>

                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-12 footer-column">

                            <div class="footer-widget links-widget ml_80">

                                <div class="widget-title">

                                    <h4>Links</h4>

                                </div>

                                <div class="widget-content">

                                    <ul class="links-list clearfix">

                                        <li><a href="index.html">Our Projects</a></li>

                                        <li><a href="index.html">About Us</a></li>

                                        <li><a href="index.html">Our Mission</a></li>

                                        <li><a href="index.html">Meet the Team</a></li>

                                        <li><a href="index.html">Contact</a></li>

                                    </ul>

                                </div>

                            </div>

                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-12 footer-column">

                            <div class="footer-widget links-widget">

                                <div class="widget-title">

                                    <h4>Explore</h4>

                                </div>

                                <div class="widget-content">

                                    <ul class="links-list clearfix">

                                        <li><a href="index.html">Site Map</a></li>

                                        <li><a href="index.html">Help Center</a></li>

                                        <li><a href="index.html">Terms of Use</a></li>

                                        <li><a href="index.html">Privacy Policy</a></li>

                                    </ul>

                                </div>

                            </div>

                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-12 footer-column">

                            <div class="footer-widget newsletter-widget">

                                <div class="widget-title">

                                    <h4>Newsletter</h4>

                                </div>

                                <div class="widget-content">

                                   <form action="https://formsubmit.co/globalacademysl@gmail.com" method="POST" />

                                        <div class="form-group">

                                            <input type="email" name="email" placeholder="Email address" required="">

                                            <button type="submit"><i class="fas fa-paper-plane"></i></button>

                                        </div>

                                        <div class="form-group">

                                            <div class="check-box">

                                                <input class="check" type="checkbox" id="checkbox">

                                                <label for="checkbox">I agree to all your terms and policies</label>

                                            </div>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="footer-bottom">

                <div class="copyright centred">

                    <p>&copy; Copyright 2025 by <a href="index.html">Colombohost</a>.com</p>

                </div>

            </div>

        </section>

        <!-- main-footer end -->





        <!--Scroll to top-->

        <button class="scroll-top scroll-to-target" data-target="html">

            <span class="fal fa-long-arrow-up"></span>

        </button>

    </div>





    <!-- jequery plugins -->

    <script src="assets/js/jquery.js"></script>

    <script src="assets/js/popper.min.js"></script>

    <script src="assets/js/bootstrap.min.js"></script>

    <script src="assets/js/owl.js"></script>

    <script src="assets/js/wow.js"></script>

    <script src="assets/js/validation.js"></script>

    <script src="assets/js/jquery.fancybox.js"></script>

    <script src="assets/js/appear.js"></script>

    <script src="assets/js/scrollbar.js"></script>



    <!-- map script -->

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA-CE0deH3Jhj6GN4YvdCFZS7DpbXexzGU"></script>

    <script src="assets/js/gmaps.js"></script>

    <script src="assets/js/map-helper.js"></script>



    <!-- main-js -->

    <script src="assets/js/script.js"></script>



</body><!-- End of .page_wrapper -->

<script>
    function validateForm() {
        // Validate Name (only letters and spaces)
        var name = document.getElementById("namex").value;
        var namePattern = /^[a-zA-Z\s]+$/;
        if (!namePattern.test(name)) {
            alert("Please enter a valid name (only letters and spaces are allowed).");
            return false;
        }

        // Validate Email (valid email format)
        var email = document.getElementById("emailx").value;
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        if (!emailPattern.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        // ...existing code...
        // Validate Phone Number (exactly 10 digits)
        var phone = document.getElementById("numberx").value.trim();
        var phonePattern = /^[0-9]{10}$/;
        if (!phonePattern.test(phone)) {
            alert("Please enter a valid phone number (exactly 10 digits).");
            return false;
        }
        // ...existing code...

        // Validate Message (only text, no numbers or special characters)
        var message = document.getElementById("messagex").value;
        var messagePattern = /^[a-zA-Z\s.,!?]+$/;
        if (!messagePattern.test(message)) {
            alert("Please enter a valid message (only letters, spaces, and punctuation marks are allowed).");
            return false;
        }

        // Validate CAPTCHA
        var captcha = document.querySelector('input[name="captcha"]').value;
        if (!captcha || captcha.length < 4) {
            alert("Please enter the CAPTCHA code correctly.");
            return false;
        }

        return true;  // Form is valid, submit the form
    }
</script>

</html>

