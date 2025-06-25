<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registration Link Expired - HCSSIS</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .expired-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                padding: 3rem;
                text-align: center;
                max-width: 600px;
                width: 90%;
            }

            .expired-icon {
                font-size: 4rem;
                color: #dc3545;
                margin-bottom: 1.5rem;
            }

            .expired-title {
                color: #333;
                margin-bottom: 1rem;
                font-weight: 300;
            }

            .expired-message {
                color: #666;
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .help-section {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 1.5rem;
                margin: 2rem 0;
                text-align: left;
            }

            .help-item {
                display: flex;
                align-items: flex-start;
                margin-bottom: 1rem;
            }

            .help-item:last-child {
                margin-bottom: 0;
            }

            .help-icon {
                color: #667eea;
                margin-right: 1rem;
                margin-top: 0.2rem;
                flex-shrink: 0;
            }

            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                padding: 12px 30px;
                border-radius: 25px;
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }

            .contact-info {
                background: #e7f3ff;
                border-left: 4px solid #0066cc;
                padding: 1rem;
                margin: 1.5rem 0;
                text-align: left;
            }
        </style>
    </head>

    <body>
        <div class="expired-container">
            <div class="expired-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <h1 class="expired-title">Registration Link Expired</h1>

            <p class="expired-message">
                {{ $message ?? 'This registration link has expired or is invalid. Registration links are valid for 7 days from when they are sent.' }}
            </p>

            <div class="help-section">
                <h5 class="mb-3"><i class="fas fa-question-circle me-2"></i>What can you do?</h5>

                <div class="help-item">
                    <i class="fas fa-envelope help-icon"></i>
                    <div>
                        <strong>Contact HCS Department</strong><br>
                        <small class="text-muted">Request a new registration link to be sent to your email
                            address.</small>
                    </div>
                </div>

                <div class="help-item">
                    <i class="fas fa-phone help-icon"></i>
                    <div>
                        <strong>Call Our Office</strong><br>
                        <small class="text-muted">Speak directly with our HCS team for immediate assistance.</small>
                    </div>
                </div>

                <div class="help-item">
                    <i class="fas fa-clock help-icon"></i>
                    <div>
                        <strong>Check Your Email</strong><br>
                        <small class="text-muted">Look for a more recent registration email that might still be
                            valid.</small>
                    </div>
                </div>
            </div>

            <div class="contact-info">
                <h6 class="mb-2"><i class="fas fa-address-card me-2"></i>HCS Contact Information</h6>
                <p class="mb-1"><strong>Email:</strong> support.hrd-bpp@arka.co.id</p>
                <p class="mb-1"><strong>Phone:</strong> Arny (213), Nisa (153)</p>
                <p class="mb-0"><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Security Note:</strong> Registration links expire for security reasons.
                This ensures that only authorized personnel can access the employee registration system.
            </div>

            {{-- <div class="mt-4">
                <a href="mailto:hr@company.com?subject=Request New Employee Registration Link"
                    class="btn btn-primary me-2">
                    <i class="fas fa-envelope me-1"></i> Request New Link
                </a>
            </div> --}}
        </div>
    </body>

</html>
