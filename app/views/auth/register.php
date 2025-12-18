<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Water Billing System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #3b82f6;
            --primary-blue-light: #60a5fa;
            --primary-blue-dark: #1d4ed8;
            --light-blue: #e0f2fe;
            --white: #ffffff;
            --gray-light: #f8fafc;
            --gray-medium: #e2e8f0;
            --gray-dark: #64748b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --rounded-sm: 0.25rem;
            --rounded-md: 0.5rem;
            --rounded-lg: 0.75rem;
            --transition: all 0.2s ease-in-out;
        }
        
        /* Hardware acceleration for the entire page */
        html, body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            background-color: #f8fafc;
        }
        
        /* Background styling */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/water_billing_system/images/registerbg.jpg');
            background-size: cover;
            background-position: center;
            filter: brightness(0.7) saturate(1.2);
            animation: backgroundPulse 15s ease-in-out infinite;
            will-change: filter;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        
        @keyframes backgroundPulse {
            0%, 100% {
                filter: brightness(0.7) saturate(1.2);
            }
            50% {
                filter: brightness(0.8) saturate(1.4);
            }
        }
        
        /* Optimize background container to reduce repaints */
        .background-container {
            will-change: transform;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(29, 78, 216, 0.7) 0%, rgba(59, 130, 246, 0.6) 100%);
            backdrop-filter: blur(2px);
            will-change: opacity;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        
        /* User icon animations */
        @keyframes iconFloat {
            0% {
                transform: translateY(-100px) rotate(0deg) translateZ(0);
                opacity: 0;
            }
            10% {
                opacity: 0.7;
            }
            90% {
                opacity: 0.7;
            }
            100% {
                transform: translateY(120vh) rotate(360deg) translateZ(0);
                opacity: 0;
            }
        }
        
        .user-icon {
            position: absolute;
            color: rgba(255, 255, 255, 0.4);
            z-index: 0;
            animation: iconFloat linear infinite;
            will-change: transform, opacity;
            transform: translateZ(0);
            backface-visibility: hidden;
            perspective: 1000px;
        }
        
        /* Floating user icons effect */
        .background-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 25%),
                        radial-gradient(circle at 80% 30%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 25%),
                        radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 25%);
            animation: iconBubbleFloat 20s ease-in-out infinite alternate;
            z-index: 2;
            pointer-events: none;
            will-change: background-position;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        
        @keyframes iconBubbleFloat {
            0% {
                background-position: 0% 0%, 0% 0%, 0% 0%;
            }
            100% {
                background-position: 10% 20%, -15% 10%, 5% -10%;
            }
        }

        /* Navigation Ribbon */
        .nav-ribbon {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            will-change: transform;
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--white);
            text-decoration: none;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .nav-logo i {
            font-size: 1.25rem;
            animation: logoWave 3s ease-in-out infinite;
            will-change: transform;
            transform: translateZ(0);
        }
        
        @keyframes logoWave {
            0%, 100% { transform: translateY(0) rotate(0) translateZ(0); }
            50% { transform: translateY(-5px) rotate(10deg) translateZ(0); }
        }

        .nav-actions {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            padding: 0.5rem 1rem;
            border-radius: var(--rounded-sm);
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            text-decoration: none;
        }

        .nav-btn-primary {
            background: linear-gradient(135deg, rgba(29, 78, 216, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .nav-btn-primary:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(29, 78, 216, 0.9) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(29, 78, 216, 0.3);
        }

        .nav-btn-outline {
            border: 1px solid rgba(255, 255, 255, 0.7);
            color: var(--white);
            background-color: rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .nav-btn-outline:hover {
            background-color: rgba(96, 165, 250, 0.5);
            transform: translateY(-2px);
        }
        
        /* Button animation effects */
        .nav-btn::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
            width: 140%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 70%);
            transform: rotate(20deg) translateZ(0);
            animation: buttonShimmer 3s linear infinite;
            will-change: transform, opacity;
            backface-visibility: hidden;
        }
        
        @keyframes buttonShimmer {
            0% {
                transform: translateY(-50%) rotate(20deg) translateZ(0);
                opacity: 0;
            }
            20% {
                opacity: 0.2;
            }
            50% {
                opacity: 0.3;
            }
            80% {
                opacity: 0.2;
            }
            100% {
                transform: translateY(50%) rotate(20deg) translateZ(0);
                opacity: 0;
            }
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem 1rem;
            position: relative;
            z-index: 1;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container-wrapper {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .form-section {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 2.5rem;
            border-radius: var(--rounded-lg);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            flex: 1;
            min-width: 300px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            will-change: transform, box-shadow;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        
        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue-light), var(--primary-blue-dark), var(--primary-blue-light));
            background-size: 200% 100%;
            animation: gradientShift 3s linear infinite;
            will-change: background-position;
        }
        
        .form-section::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: -1;
            transform: translateZ(0);
        }

        .form-section:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px) translateZ(0);
        }
        
        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        /* Removed duplicate form-section::before rule */

        .form-section h3 {
            color: var(--white);
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 0.75rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-blue-light), var(--primary-blue-dark));
            border-radius: 3px;
            animation: gradientShift 3s linear infinite;
            background-size: 200% 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            100% {
                background-position: 100% 50%;
            }
        }

        .form-section h4 {
            color: var(--white);
            margin: 1.5rem 0 1rem;
            font-size: 1.2rem;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            position: relative;
            display: inline-block;
        }
        
        .form-section h4::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-blue-light), transparent);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--white);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            letter-spacing: 0.02em;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--rounded-md);
            font-size: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            color: var(--white);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            will-change: transform, box-shadow;
            transform: translateZ(0);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-blue-light);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
            background-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px) translateZ(0);
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-group input:focus::placeholder,
        .form-group textarea:focus::placeholder {
            opacity: 0.5;
            transform: translateX(5px);
            color: rgba(255, 255, 255, 0.9);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .role-selector {
            text-align: center;
            margin-bottom: 2rem;
            background-color: rgba(255, 255, 255, 0.15);
            padding: 1.5rem;
            border-radius: var(--rounded-lg);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            will-change: transform;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        
        .role-selector::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue-light), var(--primary-blue-dark), var(--primary-blue-light));
            background-size: 200% 100%;
            animation: gradientShift 3s linear infinite;
            will-change: background-position;
        }

        .role-selector label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 500;
            color: var(--white);
            font-size: 1.1rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .role-selector select {
            padding: 0.75rem 1rem;
            border-radius: var(--rounded-md);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 1rem;
            width: 100%;
            max-width: 300px;
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--white);
            cursor: pointer;
            transition: var(--transition);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .role-selector select:focus {
            outline: none;
            border-color: var(--primary-blue-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            background-color: rgba(255, 255, 255, 0.25);
        }
        
        .role-selector select option {
            background-color: #1e3a8a;
            color: var(--white);
        }

        button {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            color: var(--white);
            padding: 1rem 1.75rem;
            border: none;
            border-radius: var(--rounded-md);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
            width: 100%;
            margin-top: 1.5rem;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            will-change: transform, box-shadow;
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        button:hover {
            background: linear-gradient(135deg, var(--primary-blue-dark) 0%, var(--primary-blue) 100%);
            transform: translateY(-3px) translateZ(0);
            box-shadow: 0 12px 30px rgba(29, 78, 216, 0.3);
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: transform 0.5s ease;
            will-change: transform;
            transform: translateZ(0);
        }
        
        button:hover::before {
            transform: translateX(200%) translateZ(0);
        }
        
        button::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
            will-change: opacity;
            transform: translateZ(0);
        }
        
        button:hover::after {
            opacity: 1;
        }

        .modal-overlay { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(2, 6, 23, 0.55), rgba(2, 6, 23, 0.35)); backdrop-filter: saturate(160%) blur(10px); z-index: 2000; }
        .modal { width: 92%; max-width: 540px; border-radius: 20px; background: radial-gradient(120% 140% at 50% 0%, #ffffff 0%, #f8fafc 60%, #f1f5f9 100%); border: 1px solid rgba(226, 232, 240, 0.7); box-shadow: 0 30px 70px rgba(2, 6, 23, 0.35), 0 1px 0 rgba(255,255,255,0.6) inset; transform: translateY(14px) scale(0.98); opacity: 0; transition: transform .28s ease, opacity .28s ease, box-shadow .28s ease; position: relative; }
        .modal.open { opacity: 1; transform: translateY(0) scale(1); box-shadow: 0 40px 80px rgba(2, 6, 23, 0.40); }
        .modal::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 10px; border-top-left-radius: 20px; border-top-right-radius: 20px; background: linear-gradient(90deg, var(--primary-blue-light), var(--primary-blue-dark), var(--primary-blue-light)); background-size: 200% 100%; animation: gradientShift 3s linear infinite; opacity: .85; }
        .modal-close { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; border: none; border-radius: 12px; background: rgba(96, 165, 250, 0.15); color: #0f172a; font-size: 20px; cursor: pointer; box-shadow: 0 6px 14px rgba(2,6,23,0.18); transition: background .2s ease, transform .2s ease; }
        .modal-close:hover { background: rgba(96, 165, 250, 0.25); transform: translateY(-2px); }
        .modal-body { padding: 32px 32px 28px; text-align: center; }
        .success-icon { width: 96px; height: 96px; border-radius: 50%; margin: 2px auto 18px; display: flex; align-items: center; justify-content: center; background: linear-gradient(180deg, #ecfdf5, #f8fafc); box-shadow: 0 12px 24px rgba(34, 197, 94, 0.20); border: 6px solid rgba(34, 197, 94, 0.15); }
        .modal.open .success-icon { transform: scale(1.02); transition: transform .25s ease; }
        .success-title { font-size: 1.28rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; letter-spacing: .2px; }
        .success-text { color: #334155; font-size: .98rem; margin-bottom: 18px; }
        .modal-actions { display: grid; grid-auto-flow: row; grid-template-columns: 1fr; gap: 12px; justify-content: center; margin-top: 10px; }
        .btn-modal-primary { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; border-radius: 12px; background: linear-gradient(135deg, var(--primary-blue-dark), var(--primary-blue)); color: #ffffff; font-weight: 600; text-decoration: none; border: none; box-shadow: 0 12px 24px rgba(29, 78, 216, .30); cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; width: 100%; min-height: 48px; }
        .btn-modal-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(29, 78, 216, .35); filter: brightness(1.03); }
        .btn-modal-neutral { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; border-radius: 12px; background: linear-gradient(135deg, #dc2626, #ef4444); color: #ffffff; border: none; font-weight: 600; cursor: pointer; box-shadow: 0 12px 24px rgba(220,38,38,0.30); transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; width: 100%; min-height: 48px; }
        .btn-modal-neutral:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(220,38,38,0.35); filter: brightness(1.03); }
        .success-tick { width: 72px; height: 72px; }
        .success-tick circle { stroke: #22c55e; stroke-width: 4; fill: none; opacity: .3; }
        .success-tick path { stroke: #22c55e; stroke-width: 5; fill: none; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 120; stroke-dashoffset: 120; animation: tick-dash .6s ease forwards .25s; }
        @keyframes tick-dash { to { stroke-dashoffset: 0; } }
        
        @keyframes userIconPulse {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }
            50% {
                transform: scale(1.5);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        .error-message, .success-message {
            padding: 1rem;
            border-radius: var(--rounded-md);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .error-message {
            color: #b91c1c;
            background-color: #fee2e2;
            border: 1px solid #fecaca;
        }

        .success-message {
            color: #166534;
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .note {
            font-size: 0.875rem;
            color: var(--gray-dark);
            margin-bottom: 1rem;
            font-style: italic;
        }

        .login-link {
            text-align: center;
            margin-top: 2.5rem;
            color: var(--gray-dark);
        }

        .login-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: var(--primary-blue-dark);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }
            .form-container-wrapper {
                flex-direction: column;
                gap: 1.5rem;
            }
            .form-section {
                padding: 1.5rem;
            }
            .page-header h2 {
                font-size: 1.75rem;
            }
            .nav-ribbon {
                padding: 1rem;
                flex-direction: column;
                gap: 0.75rem;
            }
            .nav-actions {
                width: 100%;
                justify-content: space-between;
            }
            .nav-btn {
                flex: 1;
                justify-content: center;
            }
        }

        /* Animation for form switching */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px) translateZ(0); }
            to { opacity: 1; transform: translateY(0) translateZ(0); }
        }
        .form-section {
            animation: fadeIn 0.3s ease-out forwards;
            will-change: opacity, transform;
        }
    </style>
</head>
<body>
    <div id="loader" class="loader-overlay"><div class="spinner"></div></div>
    <style>
        .loader-overlay{position:fixed;inset:0;z-index:2000;background:linear-gradient(120deg,rgba(255,255,255,.85),rgba(255,255,255,.6));backdrop-filter:saturate(180%) blur(8px);display:flex;align-items:center;justify-content:center;transition:opacity .4s ease}
        .loader-overlay.loader-hidden{opacity:0;pointer-events:none}
        .spinner{position:relative;width:10em;height:10em}
        .spinner:before{transform:rotateX(60deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateBefore infinite linear reverse}
        .spinner:after{transform:rotateX(240deg) rotateY(45deg) rotateZ(45deg);animation:750ms rotateAfter infinite linear}
        .spinner:before,.spinner:after{box-sizing:border-box;content:'';display:block;position:absolute;top:50%;left:50%;margin-top:-5em;margin-left:-5em;width:10em;height:10em;transform-style:preserve-3d;transform-origin:50%;perspective-origin:50% 50%;perspective:340px;background-size:10em 10em;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjI2NnB4IiBoZWlnaHQ9IjI5N3B4IiB2aWV3Qm94PSIwIDAgMjY2IDI5NyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyI+CiAgICA8dGl0bGU+c3Bpbm5lcjwvdGl0bGU+CiAgICA8ZGVzY3JpcHRpb24+Q3JlYXRlZCB3aXRoIFNrZXRjaCAoaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoKTwvZGVzY3JpcHRpb24+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBza2V0Y2g6dHlwZT0iTVNQYWdlIj4KICAgICAgICA8cGF0aCBkPSJNMTcxLjUwNzgxMywzLjI1MDAwMDM4IEMyMjYuMjA4MTgzLDEyLjg1NzcxMTEgMjk3LjExMjcyMiw3MS40OTEyODIzIDI1MC44OTU1OTksMTA4LjQxMDE1NSBDMjE2LjU4MjAyNCwxMzUuODIwMzEgMTg2LjUyODQwNSw5Ny4wNjI0OTY0IDE1Ni44MDA3NzQsODUuNzczNDM0NiBDMTI3LjA3MzE0Myw3NC40ODQzNzIxIDc2Ljg4ODQ2MzIsODQuMjE2MTQ2MiA2MC4xMjg5MDY1LDEwOC40MTAxNTMgQy0xNS45ODA0Njg1LDIxOC4yODEyNDcgMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IDE0NS4yNzczNDQsMjk2LjY2Nzk2OCBDMTQ1LjI3NzM0NCwyOTYuNjY3OTY4IC0yNS40NDkyMTg3LDI1Ny4yNDIxOTggMy4zOTg0Mzc1LDEwOC40MTAxNTUgQzE2LjMwNzA2NjEsNDEuODExNDE3NCA4NC43Mjc1ODI5LC0xMS45OTIyOTg1IDE3MS41MDc4MTMsMy4yNTAwMDAzOCBaIiBpZD0iUGF0aC0xIiBmaWxsPSIjMDAwMDAwIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==)}
        @keyframes rotateBefore{from{transform:rotateX(60deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(60deg) rotateY(45deg) rotateZ(-360deg)}}
        @keyframes rotateAfter{from{transform:rotateX(240deg) rotateY(45deg) rotateZ(0deg)}to{transform:rotateX(240deg) rotateY(45deg) rotateZ(360deg)}}
    </style>
    <script>
        window.addEventListener('load',()=>{const l=document.getElementById('loader');if(l){setTimeout(()=>{l.classList.add('loader-hidden');setTimeout(()=>{try{l.remove()}catch(e){}},600);},1500)}});
    </script>
    <!-- Background with user icon animations -->
    <div class="background-container">
        <div class="background-image"></div>
        <div class="overlay"></div>
        
        <!-- User icons animation -->
        <i class="fas fa-user user-icon" style="left: 10%; font-size: 20px; animation-duration: 15s;"></i>
        <i class="fas fa-user-tie user-icon" style="left: 20%; font-size: 24px; animation-duration: 12s; animation-delay: 1s;"></i>
        <i class="fas fa-user-shield user-icon" style="left: 35%; font-size: 18px; animation-duration: 18s; animation-delay: 2s;"></i>
        <i class="fas fa-user-check user-icon" style="left: 50%; font-size: 22px; animation-duration: 14s; animation-delay: 0.5s;"></i>
        <i class="fas fa-user-cog user-icon" style="left: 65%; font-size: 26px; animation-duration: 16s; animation-delay: 3s;"></i>
        <i class="fas fa-user-graduate user-icon" style="left: 80%; font-size: 19px; animation-duration: 13s; animation-delay: 2.5s;"></i>
        <i class="fas fa-user-astronaut user-icon" style="left: 90%; font-size: 21px; animation-duration: 17s; animation-delay: 1.5s;"></i>
    </div>
    
    <!-- Navigation Ribbon -->
    <nav class="nav-ribbon">
        <a href="index.php" class="nav-logo">
            <i class="fas fa-tint"></i>
            <span>AquaBill</span>
        </a>
        <div class="nav-actions">
            <a href="index.php?page=login" class="nav-btn nav-btn-outline">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="index.php?page=register" class="nav-btn nav-btn-primary">
                <i class="fas fa-user-plus"></i> Register
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h2>Create Your Account</h2>
                <p>Join AquaBill to manage your water services efficiently.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
            <?php endif; ?>

            <div class="role-selector">
                <label for="roleSelect">Register as:</label>
                <select id="roleSelect" class="form-control">
                    <option value="client">Client</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form-container-wrapper">
                <!-- Client Registration Form -->
                <div id="clientForm" class="form-section">
                    <h3>Client Registration</h3>
                    <form action="index.php?page=register" method="POST" id="mainRegisterForm">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <h4>Personal Details</h4>
                        <div class="form-group">
                            <label for="full_name">Full Name:</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone:</label>
                            <input type="text" id="contact_phone" name="contact_phone" placeholder="e.g., 254712345678" required>
                        </div>
                        <button type="submit" name="register_client">Register Client</button>
                    </form>
                </div>

                <!-- Admin Registration Form (Hidden by default) -->
                <div id="adminForm" class="form-section" style="display: none;">
                    <h3>Admin Registration</h3>
                    <form action="index.php?page=register" method="POST" id="adminRegisterForm">
                        <div class="form-group">
                            <label for="admin_username">Username:</label>
                            <input type="text" id="admin_username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_email">Email:</label>
                            <input type="email" id="admin_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_password">Password:</label>
                            <input type="password" id="admin_password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_confirm_password">Confirm Password:</label>
                            <input type="password" id="admin_confirm_password" name="confirm_password" required>
                        </div>
                        <h4>Personal Details (Admin)</h4>
                        <div class="form-group">
                            <label for="admin_full_name">Full Name:</label>
                            <input type="text" id="admin_full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_address">Address:</label>
                            <textarea id="admin_address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="admin_contact_phone">Contact Phone:</label>
                            <input type="text" id="admin_contact_phone" name="contact_phone" placeholder="e.g., 254712345678" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_key">Admin Key:</label>
                            <input type="password" id="admin_key" name="admin_key" required>
                            <p class="note">Required for admin account creation.</p>
                        </div>
                        <button type="submit" name="register_admin">Register Admin</button>
                    </form>
                </div>
            </div>

            <div class="login-link">
                Already have an account? <a href="index.php?page=login">Login here</a>
            </div>
            <div style="margin-top:0.75rem;text-align:center;color:#6b7280;font-size:0.82rem;">&copy; <?php echo date('Y'); ?> AquaBill Water Billing System. All rights reserved.</div>
            <div style="margin-top:0.25rem;text-align:center;color:#94a3b8;font-size:0.74rem;font-weight:500;">
                <?php
                    $p = __DIR__ . '/../../config/.owner';
                    $t = is_file($p) ? trim((string)file_get_contents($p)) : '';
                    echo htmlspecialchars($t !== '' ? $t : '');
                ?>
            </div>
            <div id="successModalOverlay" class="modal-overlay">
                <div id="successModal" class="modal">
                    <div class="modal-body">
                        <div class="success-icon">
                            <svg class="success-tick" viewBox="0 0 72 72">
                                <circle cx="36" cy="36" r="34"></circle>
                                <path d="M20 37 L31 48 L52 26"></path>
                            </svg>
                        </div>
                        <div class="success-title">Registration Successful</div>
                        <p id="successModalMsg" class="success-text"></p>
                        <div class="modal-actions">
                            <a href="index.php?page=login" id="successModalLogin" class="btn-modal-primary">Login Now</a>
                            <button id="successModalOk" class="btn-modal-neutral">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <button type="button" id="reportIssueBtn" class="btn btn-outline" style="width: auto; background: none; border: 1px solid var(--gray-medium); color: var(--gray-dark); padding: 0.85rem 1.75rem; border-radius: var(--rounded-md); transition: all 0.3s ease; position: relative; overflow: hidden;"><i class="fas fa-bug mr-2"></i> Report an Issue</button>
            </div>
        </div>
        
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('roleSelect');
            const clientForm = document.getElementById('clientForm');
            const adminForm = document.getElementById('adminForm');
            const modalOverlay = document.getElementById('successModalOverlay');
            const modal = document.getElementById('successModal');
            const modalMsg = document.getElementById('successModalMsg');
            const modalClose = document.getElementById('successModalClose');
            const modalOk = document.getElementById('successModalOk');

            function toggleForms() {
                if (roleSelect.value === 'client') {
                    clientForm.style.display = 'block';
                    adminForm.style.display = 'none';
                    // Reset admin form fields when switching away
                    adminForm.querySelectorAll('input').forEach(input => input.value = '');
                    adminForm.querySelectorAll('textarea').forEach(textarea => textarea.value = '');
                } else if (roleSelect.value === 'admin') {
                    clientForm.style.display = 'none';
                    adminForm.style.display = 'block';
                    // Reset client form fields when switching away
                    clientForm.querySelectorAll('input').forEach(input => input.value = '');
                    clientForm.querySelectorAll('textarea').forEach(textarea => textarea.value = '');
                }
            }

            // Initial call to set the correct form visibility based on default selection
            toggleForms();

            // Add event listener for changes in the role selection
            roleSelect.addEventListener('change', toggleForms);

            // Report issue button functionality
            document.getElementById('reportIssueBtn').addEventListener('click', function(e) {
                e.preventDefault();
                // Replace alert with a custom modal or message box if needed in a real app
                alert('Issue reporting feature will be implemented soon. Thank you for your patience!');
            });
            function openSuccessModal(message) {
                modalMsg.textContent = message || 'You can now log in.';
                modalOverlay.style.display = 'flex';
                requestAnimationFrame(function(){ modal.classList.add('open'); });
            }
            function closeSuccessModal() {
                modal.classList.remove('open');
                setTimeout(function(){ modalOverlay.style.display = 'none'; }, 250);
            }
            if (typeof modalClose !== 'undefined' && modalClose) { modalClose.addEventListener('click', closeSuccessModal); }
            modalOk.addEventListener('click', closeSuccessModal);
            const registrationSuccessMsg = <?php echo json_encode(!empty($success) ? $success : ''); ?>;
            if (registrationSuccessMsg) {
                openSuccessModal(registrationSuccessMsg);
            }
            
            // Create user icons animation - optimized version
            function createUserIcons() {
                const backgroundContainer = document.querySelector('.background-container');
                const icons = ['fa-user', 'fa-user-tie', 'fa-user-shield', 'fa-user-check', 'fa-user-cog'];
                const numIcons = 10; // Reduced number of icons
                const fragment = document.createDocumentFragment(); // Use document fragment for better performance
                
                for (let i = 0; i < numIcons; i++) {
                    const icon = document.createElement('i');
                    const randomIcon = icons[Math.floor(Math.random() * icons.length)];
                    icon.className = `fas ${randomIcon} user-icon`;
                    
                    // Random positioning
                    icon.style.left = `${Math.random() * 100}%`;
                    icon.style.fontSize = `${Math.random() * 20 + 15}px`;
                    icon.style.opacity = `${Math.random() * 0.5 + 0.2}`;
                    
                    // Random animation duration - slightly longer to reduce number of animations
                    const duration = Math.random() * 20 + 15;
                    icon.style.animationDuration = `${duration}s`;
                    
                    // Random delay
                    icon.style.animationDelay = `${Math.random() * 5}s`;
                    
                    // Add transform property for hardware acceleration
                    icon.style.transform = 'translateZ(0)';
                    
                    fragment.appendChild(icon);
                }
                
                // Append all icons at once for better performance
                backgroundContainer.appendChild(fragment);
            }
            
            // Delay icon creation slightly to prioritize page load
            setTimeout(createUserIcons, 100);
        });
    </script>
</body>
</html>
