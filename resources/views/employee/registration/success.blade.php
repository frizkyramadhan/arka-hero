<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registration Submitted - HCSSIS</title>

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

            .success-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                padding: 3rem;
                text-align: center;
                max-width: 600px;
                width: 90%;
            }

            .success-icon {
                font-size: 4rem;
                color: #28a745;
                margin-bottom: 1.5rem;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.1);
                }

                100% {
                    transform: scale(1);
                }
            }

            .success-title {
                color: #333;
                margin-bottom: 1rem;
                font-weight: 300;
            }

            .success-message {
                color: #666;
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .next-steps {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 1.5rem;
                margin: 2rem 0;
                text-align: left;
            }

            .step-item {
                display: flex;
                align-items: flex-start;
                margin-bottom: 1rem;
            }

            .step-item:last-child {
                margin-bottom: 0;
            }

            .step-number {
                background: #667eea;
                color: white;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.875rem;
                margin-right: 1rem;
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
        </style>
    </head>

    <body>
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 class="success-title">Registration Submitted Successfully!</h1>

            <p class="success-message">
                {{ $message ?? 'Your employee registration has been submitted and is now being reviewed by our HR team. We will contact you shortly with updates about your application status.' }}
            </p>

            <div class="next-steps">
                <h5 class="mb-3"><i class="fas fa-list-ul me-2"></i>What happens next?</h5>

                <div class="step-item">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Review Process</strong><br>
                        <small class="text-muted">Our HCS team will review your submitted information and documents
                            within 2-3 business days.</small>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Verification</strong><br>
                        <small class="text-muted">We may contact you for additional information or document verification
                            if needed.</small>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Notification</strong><br>
                        <small class="text-muted">You will receive an email notification about the approval status and
                            next steps.</small>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">4</div>
                    <div>
                        <strong>Onboarding</strong><br>
                        <small class="text-muted">Once approved, you'll receive detailed instructions for your
                            onboarding process.</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Important:</strong> Please save this page or take a screenshot for your records.
                If you have any questions, please contact our HCS department.
            </div>

            <div class="mt-4">
                <button onclick="window.print()" class="btn btn-outline-primary me-2">
                    <i class="fas fa-print me-1"></i> Print This Page
                </button>
                <a href="mailto:support.hrd-bpp@arka.co.id" class="btn btn-primary">
                    <i class="fas fa-envelope me-1"></i> Contact HCS
                </a>
            </div>
        </div>
    </body>

</html>
