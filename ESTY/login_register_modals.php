<?php
// Login and Register Modals - Enhanced Design
// This file should be included in pages where you want to use the modals
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 900px;">
    <div class="modal-content border-0" style="border-radius: 20px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);">
      <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1000; background: none; opacity: 0.8;"></button>
      
      <div class="row g-0">
        <!-- Left side - Decorative with animation (hidden on mobile) -->
        <div class="col-12 col-md-5 d-none d-md-flex" style="background: linear-gradient(135deg, #c9a646 0%, #d4b85a 50%, #8b6b2d 100%); border-radius: 20px 0 0 20px; align-items: center; justify-content: center; min-height: 400px; padding: 30px 25px; position: relative; overflow: hidden;">
          <!-- Animated background elements -->
          <div style="position: absolute; width: 300px; height: 300px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; top: -100px; right: -100px;"></div>
          <div style="position: absolute; width: 200px; height: 200px; background: rgba(255, 255, 255, 0.05); border-radius: 50%; bottom: -50px; left: -50px;"></div>
          
          <div class="text-center text-white" style="position: relative; z-index: 1;">
            <div style="font-size: 80px; margin-bottom: 20px; animation: float 3s ease-in-out infinite;">
              <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px; letter-spacing: 0.5px;">Welcome Back!</h3>
            <p style="font-size: 13px; opacity: 0.95; margin-bottom: 0; line-height: 1.5;">Sign in to your account</p>
          </div>
        </div>
        
        <!-- Right side - Form with responsive styling -->
        <div class="col-12 col-md-7">
          <div style="padding: 30px 25px; display: flex; flex-direction: column; justify-content: center;">
            <div style="margin-bottom: 20px;">
              <h5 style="font-size: 22px; font-weight: 800; margin-bottom: 6px; color: #2c2416; letter-spacing: -0.5px;">Login</h5>
              <p style="color: #999; font-size: 13px; margin-bottom: 0;">Welcome to your sanctuary</p>
            </div>
            
            <div id="loginMessage"></div>
            
            <form id="loginForm" method="POST" action="process_login.php">
              <div class="mb-3">
                <label for="loginEmail" class="form-label fw-600" style="color: #2c2416; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Email Address</label>
                <input type="email" class="form-control" id="loginEmail" name="email" placeholder="your.email@example.com" required style="border-radius: 10px; border: 2px solid #e8e8e8; padding: 11px 14px; font-size: 14px; transition: all 0.3s ease; background: #fafafa;">
              </div>
              
              <div class="mb-3">
                <label for="loginPassword" class="form-label fw-600" style="color: #2c2416; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Password</label>
                <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter a password" required style="border-radius: 10px; border: 2px solid #e8e8e8; padding: 11px 14px; font-size: 14px; transition: all 0.3s ease; background: #fafafa;">
              </div>
              
              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="rememberMe" name="remember" style="width: 16px; height: 16px; cursor: pointer; border: 2px solid #e8e8e8; border-radius: 4px;">
                  <label class="form-check-label" for="rememberMe" style="cursor: pointer; color: #666; font-size: 13px; margin-left: 6px;">Keep me signed in</label>
                </div>
              </div>
              
              <button type="submit" class="btn w-100 mb-2" style="background: linear-gradient(135deg, #c9a646 0%, #b8944f 100%); color: white; font-weight: 700; padding: 11px 20px; border-radius: 10px; border: none; font-size: 14px; letter-spacing: 0.5px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(201, 166, 70, 0.3);">
                <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
              </button>
            </form>
            
            <div style="text-align: center; margin-top: 18px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
              <p style="margin-bottom: 10px; font-size: 12px; color: #666;">
                <a href="#" style="color: #c9a646; text-decoration: none; font-weight: 600; transition: all 0.2s;" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot your password?</a>
              </p>
              <p style="margin-bottom: 0; color: #666; font-size: 12px;">
                Don't have an account? 
                <a href="#" style="color: #c9a646; text-decoration: none; font-weight: 700;" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">Create one</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 900px;">
    <div class="modal-content border-0" style="border-radius: 20px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);">
      <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1000; background: none; opacity: 0.8;"></button>
      
      <div class="row g-0">
        <!-- Left side - Decorative with animation (hidden on mobile) -->
        <div class="col-12 col-md-5 d-none d-md-flex" style="background: linear-gradient(135deg, #e75480 0%, #f0678d 50%, #d4397e 100%); border-radius: 20px 0 0 20px; align-items: center; justify-content: center; min-height: 500px; padding: 30px 25px; position: relative; overflow: hidden;">
          <!-- Animated background elements -->
          <div style="position: absolute; width: 300px; height: 300px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; top: -100px; right: -100px;"></div>
          <div style="position: absolute; width: 200px; height: 200px; background: rgba(255, 255, 255, 0.05); border-radius: 50%; bottom: -50px; left: -50px;"></div>
          
          <div class="text-center text-white" style="position: relative; z-index: 1;">
            <div style="font-size: 80px; margin-bottom: 20px; animation: float 3s ease-in-out infinite; animation-delay: 0.5s;">
              <i class="bi bi-person-plus"></i>
            </div>
            <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px; letter-spacing: 0.5px;">Join Us Today!</h3>
            <p style="font-size: 13px; opacity: 0.95; margin-bottom: 0; line-height: 1.5;">Explore premium scents</p>
          </div>
        </div>
        
        <!-- Right side - Form -->
        <div class="col-12 col-md-7">
          <div style="padding: 30px 25px;">
            <div style="margin-bottom: 20px;">
              <h5 style="font-size: 22px; font-weight: 800; margin-bottom: 6px; color: #2c2416; letter-spacing: -0.5px;">Create Account</h5>
              <p style="color: #999; font-size: 13px; margin-bottom: 0;">Start your scent journey</p>
            </div>
            
            <div id="registerMessage"></div>
            
            <form id="registerForm" method="POST" action="process_register.php">
              <div class="mb-3">
                <label for="registerUsername" class="form-label fw-600" style="color: #2c2416; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">Username</label>
                <input type="text" class="form-control form-control-lg" id="registerUsername" name="username" placeholder="Enter a username" required minlength="6" maxlength="20" pattern="^[a-zA-Z0-9_-]+$" style="border-radius: 12px; border: 2px solid #e8e8e8; padding: 13px 16px; font-size: 14px; transition: all 0.3s ease; background: #fafafa;">
                <div id="usernameError" style="margin-top: 10px; display: none; background: #ffebee; border-left: 4px solid #d32f2f; padding: 12px 14px; border-radius: 4px;">
                  <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <i class="bi bi-exclamation-circle-fill" style="color: #d32f2f; margin-top: 2px; font-size: 16px; flex-shrink: 0;"></i>
                    <div style="color: #c62828; font-size: 13px; font-weight: 500;">It looks like you entered the wrong info. Please be sure to use a valid username.</div>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="registerEmail" class="form-label fw-600" style="color: #2c2416; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Email Address</label>
                <input type="email" class="form-control" id="registerEmail" name="email" placeholder="your.email@example.com" required style="border-radius: 10px; border: 2px solid #e8e8e8; padding: 11px 14px; font-size: 14px; transition: all 0.3s ease; background: #fafafa;">
              </div>
              
              <div class="mb-3">
                <label for="registerPassword" class="form-label fw-600" style="color: #2c2416; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Password</label>
                <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Enter a password" required minlength="8" style="border-radius: 10px; border: 2px solid #e8e8e8; padding: 11px 14px; font-size: 14px; transition: all 0.3s ease; background: #fafafa;">
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Minimum 8 characters</small>
              </div>
              
              <div class="mb-3">
                <label for="registerConfirmPassword" class="form-label fw-600" style="color: #2c2416; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Confirm Password</label>
                <input type="password" class="form-control" id="registerConfirmPassword" name="confirm_password" placeholder="Enter a password" required style="border-radius: 10px; border: 2px solid #e8e8e8; padding: 11px 14px; font-size: 14px; transition: all 0.3s ease; background: #fafafa;">
              </div>
              
              <button type="submit" class="btn w-100 mb-2" style="background: linear-gradient(135deg, #e75480 0%, #d9457a 100%); color: white; font-weight: 700; padding: 11px 20px; border-radius: 10px; border: none; font-size: 14px; letter-spacing: 0.5px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(231, 84, 128, 0.3);">
                <i class="bi bi-person-plus me-2"></i> Create Account
              </button>
            </form>
            
            <div style="text-align: center; margin-top: 18px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
              <p style="margin-bottom: 0; color: #666; font-size: 12px;">
                Already have an account? 
                <a href="#" style="color: #e75480; text-decoration: none; font-weight: 700;" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Sign in</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 900px;">
    <div class="modal-content border-0" style="border-radius: 20px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);">
      <button type="button" class="btn-close position-absolute top-3 end-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1000; background: none; opacity: 0.8;"></button>
      
      <div class="row g-0">
        <!-- Left side - Decorative with animation (hidden on mobile) -->
        <div class="col-12 col-md-5 d-none d-md-flex" style="background: linear-gradient(135deg, #c9a646 0%, #d4b85a 50%, #8b6b2d 100%); border-radius: 20px 0 0 20px; align-items: center; justify-content: center; min-height: 400px; padding: 30px 25px; position: relative; overflow: hidden;">
          <!-- Animated background elements -->
          <div style="position: absolute; width: 300px; height: 300px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; top: -100px; right: -100px;"></div>
          <div style="position: absolute; width: 200px; height: 200px; background: rgba(255, 255, 255, 0.05); border-radius: 50%; bottom: -50px; left: -50px;"></div>
          
          <div class="text-center text-white" style="position: relative; z-index: 1;">
            <div style="font-size: 80px; margin-bottom: 20px; animation: float 3s ease-in-out infinite;">
              <i class="bi bi-key"></i>
            </div>
            <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px; letter-spacing: 0.5px;">Reset Your Password</h3>
            <p style="font-size: 13px; opacity: 0.95; margin-bottom: 0; line-height: 1.5;">We'll help you regain access</p>
          </div>
        </div>
        
        <!-- Right side - Form with responsive styling -->
        <div class="col-12 col-md-7">
          <div style="padding: 30px 25px; display: flex; flex-direction: column; justify-content: center;">
            <div style="margin-bottom: 20px;">
              <h5 style="font-size: 22px; font-weight: 800; margin-bottom: 6px; color: #2c2416; letter-spacing: -0.5px;">Forgot Password</h5>
              <p style="color: #999; font-size: 13px; margin-bottom: 0;">Enter your email to recover your account</p>
            </div>
            
            <div id="forgotPasswordMessage"></div>
            
            <form id="forgotPasswordForm" method="POST" action="forgot_password.php">
              <div class="mb-3">
                <label for="forgotEmail" class="form-label fw-600" style="color: #2c2416; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Email Address</label>
                <input type="email" class="form-control" id="forgotEmail" name="email" placeholder="your@email.com" required style="border-radius: 10px; border: 2px solid #e8e8e8; padding: 11px 14px; font-size: 14px; transition: all 0.3s ease; background: #fafafa;">
              </div>
              
              <button type="submit" class="btn w-100 mb-2" style="background: linear-gradient(135deg, #c9a646 0%, #b8944f 100%); color: white; font-weight: 700; padding: 11px 20px; border-radius: 10px; border: none; font-size: 14px; letter-spacing: 0.5px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(201, 166, 70, 0.3);">
                <i class="bi bi-envelope-check me-2"></i> Send Recovery Link
              </button>
            </form>
            
            <div style="text-align: center; margin-top: 18px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
              <p style="margin-bottom: 0; color: #666; font-size: 12px;">
                Remember your password? 
                <a href="#" style="color: #c9a646; text-decoration: none; font-weight: 700;" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Sign in</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes float {
    0%, 100% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-20px);
    }
  }
  
  .form-control {
    transition: all 0.3s ease;
  }
  
  .form-control:focus {
    border-color: #c9a646 !important;
    box-shadow: 0 0 0 0.2rem rgba(201, 166, 70, 0.1) !important;
    background: white !important;
  }
  
  #registerModal .form-control:focus {
    border-color: #e75480 !important;
    box-shadow: 0 0 0 0.2rem rgba(231, 84, 128, 0.1) !important;
  }
  
  /* Username field validation styles */
  #registerUsername:valid {
    border-color: #10b981 !important;
  }
  
  #registerUsername:invalid:not(:placeholder-shown) {
    border-color: #ef4444 !important;
  }
  
  #usernameValidation {
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #f0f0f0;
  }
  
  .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2) !important;
  }
  
  .btn:active {
    transform: translateY(-1px);
  }
  
  #loginMessage, #registerMessage {
    margin-bottom: 1.5rem;
  }
  
  .alert-in-modal {
    border-radius: 12px;
    border: none;
    margin-bottom: 1rem;
    padding: 13px 16px;
    font-size: 14px;
    animation: slideIn 0.3s ease;
  }
  
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @media (max-width: 767px) {
    .modal-dialog {
      margin: 10px;
      max-width: 100%;
    }
    
    .modal-content {
      border-radius: 15px !important;
    }
    
    .row.g-0 {
      flex-direction: column;
    }
  }
  
  @media (max-width: 576px) {
    .modal-content {
      border-radius: 12px !important;
    }
    
    .modal-header {
      padding: 15px 20px;
    }
  }
  
  /* Fix modal scrolling for register modal */
  #registerModal .modal-dialog-scrollable {
    max-height: 90vh;
  }
  
  #registerModal .col-12.col-md-7 {
    overflow-y: auto;
    max-height: 90vh;
  }
  
  #registerModal .row.g-0 {
    max-height: 90vh;
  }
  
  /* Ensure form content is scrollable */
  #registerModal .modal-dialog-scrollable .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
  }
  
  #registerModal .row.g-0 {
    flex: 1;
    min-height: 0;
  }
  
  #registerModal .col-12.col-md-7 {
    overflow-y: auto;
  }
</style>

<script>
  // Handle Login Form
  document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    
    if (!email || !password) {
      showLoginMessage('Please fill in all fields', 'danger');
      return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Signing in...';
    
    // Send AJAX request to login
    fetch('process_login.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showLoginMessage('✓ Login successful! Redirecting...', 'success');
        setTimeout(() => {
          window.location.href = data.redirect || 'verify_login_otp.php';
        }, 1500);
      } else {
        showLoginMessage(data.message || 'Login failed', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i> Sign In';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showLoginMessage('An error occurred. Please try again.', 'danger');
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i> Sign In';
    });
  });
  
  // Username validation in real-time
  const usernameInput = document.getElementById('registerUsername');
  
  usernameInput.addEventListener('input', function() {
    const username = this.value;
    const errorDiv = document.getElementById('usernameError');
    
    // Validation checks
    const lengthValid = username.length >= 6 && username.length <= 20;
    const charsValid = /^[a-zA-Z0-9_-]*$/.test(username);
    const noSpaces = !/\s/.test(username);
    const isValid = lengthValid && charsValid && noSpaces && username.length > 0;
    
    // Show/hide error message
    if (username.length > 0 && !isValid) {
      errorDiv.style.display = 'block';
      this.style.borderColor = '#ef4444';
      this.style.backgroundColor = '#fef2f2';
    } else {
      errorDiv.style.display = 'none';
      if (isValid) {
        this.style.borderColor = '#10b981';
        this.style.backgroundColor = '#f0fdf4';
      } else {
        this.style.borderColor = '#e8e8e8';
        this.style.backgroundColor = '#fafafa';
      }
    }
  });
  
  function updateCheckmark(elementId, isValid) {
    const element = document.getElementById(elementId);
    const icon = element.querySelector('i');
    
    if (isValid) {
      icon.className = 'bi bi-check-circle-fill';
      icon.style.color = '#10b981';
      element.style.color = '#10b981';
    } else {
      icon.className = 'bi bi-circle';
      icon.style.color = '#ddd';
      element.style.color = '#999';
    }
  }
  
  // Handle Register Form
  document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('registerUsername').value.trim();
    const email = document.getElementById('registerEmail').value.trim();
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('registerConfirmPassword').value;
    
    // Enhanced username validation
    if (!username) {
      showRegisterMessage('Please enter a username', 'danger');
      return;
    }
    
    if (username.length < 6 || username.length > 20) {
      showRegisterMessage('Username must be between 6 and 20 characters', 'danger');
      return;
    }
    
    if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
      showRegisterMessage('Username can only contain letters, numbers, underscores, and hyphens', 'danger');
      return;
    }
    
    if (/\s/.test(username)) {
      showRegisterMessage('Username cannot contain spaces', 'danger');
      return;
    }
    
    if (!email || !password || !confirmPassword) {
      showRegisterMessage('Please fill in all fields', 'danger');
      return;
    }
    
    if (password !== confirmPassword) {
      showRegisterMessage('Passwords do not match', 'danger');
      return;
    }
    
    if (password.length < 8) {
      showRegisterMessage('Password must be at least 8 characters', 'danger');
      return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Creating account...';
    
    // Send AJAX request to register
    fetch('process_register.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'username=' + encodeURIComponent(username) + '&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password) + '&confirm_password=' + encodeURIComponent(confirmPassword)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showRegisterMessage('✓ Account created! Redirecting...', 'success');
        setTimeout(() => {
          window.location.href = data.redirect || 'verify_registration_otp.php';
        }, 1500);
      } else {
        showRegisterMessage(data.message || 'Registration failed', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-person-plus me-2"></i> Create Account';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showRegisterMessage('An error occurred. Please try again.', 'danger');
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-person-plus me-2"></i> Create Account';
    });
  });
  
  function showLoginMessage(message, type) {
    const messageDiv = document.getElementById('loginMessage');
    messageDiv.innerHTML = `
      <div class="alert alert-${type} alert-in-modal alert-dismissible fade show" role="alert">
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}" style="margin-right: 8px;"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
  }
  
  function showRegisterMessage(message, type) {
    const messageDiv = document.getElementById('registerMessage');
    messageDiv.innerHTML = `
      <div class="alert alert-${type} alert-in-modal alert-dismissible fade show" role="alert">
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}" style="margin-right: 8px;"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
  }
  
  // Clear messages when modal is closed
  document.getElementById('loginModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('loginMessage').innerHTML = '';
    document.getElementById('loginForm').reset();
    const submitBtn = document.querySelector('#loginForm button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i> Sign In';
  });
  
  document.getElementById('registerModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('registerMessage').innerHTML = '';
    document.getElementById('registerForm').reset();
    const submitBtn = document.querySelector('#registerForm button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-person-plus me-2"></i> Create Account';
    
    // Reset username input styling and error message
    const usernameInput = document.getElementById('registerUsername');
    usernameInput.style.borderColor = '#e8e8e8';
    usernameInput.style.backgroundColor = '#fafafa';
    document.getElementById('usernameError').style.display = 'none';
  });
  
  // Handle Forgot Password Form
  document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('forgotEmail').value.trim();
    
    if (!email) {
      showForgotPasswordMessage('Please enter your email address', 'danger');
      return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sending...';
    
    // Send AJAX request to forgot_password.php
    fetch('forgot_password.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showForgotPasswordMessage('✓ Recovery link sent! Check your email', 'success');
        setTimeout(() => {
          bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
          document.getElementById('forgotPasswordForm').reset();
        }, 2000);
      } else {
        showForgotPasswordMessage(data.message || 'Failed to send recovery link', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-envelope-check me-2"></i> Send Recovery Link';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showForgotPasswordMessage('An error occurred. Please try again.', 'danger');
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-envelope-check me-2"></i> Send Recovery Link';
    });
  });
  
  function showForgotPasswordMessage(message, type) {
    const messageDiv = document.getElementById('forgotPasswordMessage');
    messageDiv.innerHTML = `
      <div class="alert alert-${type} alert-in-modal alert-dismissible fade show" role="alert">
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}" style="margin-right: 8px;"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
  }
  
  // Clear messages when forgot password modal is closed
  document.getElementById('forgotPasswordModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('forgotPasswordMessage').innerHTML = '';
    document.getElementById('forgotPasswordForm').reset();
    const submitBtn = document.querySelector('#forgotPasswordForm button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-envelope-check me-2"></i> Send Recovery Link';
  });
</script>

