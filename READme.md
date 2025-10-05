# Esty Scents – Web-Based Ordering System

**Company:** Esty Scents  
**Developed for:** Fragrance and Self-Care Product Business  
**Institution:** University of Caloocan City  

---

## 🌐 Project Overview

**Esty Scents** is a modern **web-based e-commerce platform** developed to sell handcrafted perfumes, scented oils, home fragrances, and wellness items.  

The system enables customers to browse collections, place orders, make secure payments, and track deliveries in real-time.  
Administrators can manage products, inventory, promotions, reports, and customer support through an intuitive admin dashboard.

---

## 🧭 Scope

### **Customer Portal**
- User registration and profile management  
- Browse and search product catalog  
- Add products to cart and complete orders  
- Track order delivery and download invoices  
- Submit product reviews or support requests  

### **Admin Portal**
- Manage products, categories, and inventory  
- Process orders and handle returns/refunds  
- Manage discount codes and promotions  
- View sales analytics and customer insights  
- Handle customer communication and support tickets  

---

## ⚙️ Technical Infrastructure

| Component | Technology |
|------------|-------------|
| **Frontend** | CSS Frameworks |
| **Backend** | PHP |
| **Database** | MySQL |
| **Payment Gateway** | PayMongo |
| **Notifications** | SMS + Email (via SMTP/Twilio) |
| **Design** | Responsive (Mobile & Tablet Friendly) |

---

## 💡 Functional Features

### **Customer Portal**

#### 🔐 Account Management
- User registration & login with email/OTP verification  
- Guest checkout option  +!!
- Password recovery & 2FA support  
- Profile editing and saved addresses  

#### 🛍️ Product Catalog
- Product listing by category, brand, and popularity  
- Search and filter (price, ratings, stock)  
- Product detail page with reviews, ratings, and photos  
- “Add to Wishlist” and “Compare Products” options  

#### 🧾 Shopping Cart & Checkout
- Add, remove, and update cart items  
- Apply coupons or discount codes  
- Multi-step checkout process  
- Choose delivery method (standard or express)  
- Order summary and estimated delivery  

#### 💳 Payment System
- Integration with PayMongo, GCash, and COD  
- Real-time payment status updates  
- Digital receipt and invoice generation  
- Refund request and tracking  

#### 🚚 Order Tracking & History
- Track order status (Pending → Packed → Shipped → Delivered)  
- Order confirmation via SMS/Email  
- View past orders and reorder option  
- Cancel or return orders (if applicable)  

#### 💬 Customer Support
- Submit and track support tickets  
- Manage personal info  
- Submit product reviews  

---

### **Admin Portal**

#### 🧠 Secure Admin Login
- Role-based access (Admin, Product Manager, Support)  
- Session timeout for added security  

#### 📦 Product & Inventory Management
- Add/edit/delete products and categories  
- Upload product images and scent descriptions  
- Monitor inventory with low-stock alerts  

#### 📬 Order Management
- View and manage all orders by status  
- Update tracking information  
- Process cancellations or refunds  
- Generate order and sales reports  

#### 🎯 Promotions & Marketing
- Create and manage discount codes  
- Schedule flash sales or promotions  
- Newsletter integration (Mailchimp, etc.)  

#### 📊 Reports & Analytics
- Sales reports by date, product, or category  
- Inventory summary  
- Customer activity tracking  
- Order fulfillment metrics  

---

## 🚫 Non-Functional Features

| Category | Description |
|-----------|-------------|
| **Usability & Design** | Clean, intuitive, and responsive UI for desktop, tablet, and mobile. |
| **Performance** | Optimized load time (< 3 seconds). Handles moderate concurrent users. |
| **Security** | HTTPS encryption, SQL Injection & XSS protection, secure session handling. |
| **Reliability & Availability** | 99.9% uptime target to ensure customers can order anytime. |
| **Scalability** | Designed for growth without requiring a full system overhaul. |
| **Maintainability** | Well-structured and documented code for easier updates. |
| **Compliance** | Adheres to Philippine Data Privacy Act for customer information security. |

---

## ⚠️ Limitations

### Manual Order and Marketing Processes
- The system will not have automated features for bulk order fulfillment or batch shipping.  
- All email marketing campaigns will require manual management through a third-party service.

### Basic Analytics Only
- The platform will not include advanced analytics such as AI-based product recommendations or predictive insights into customer behavior.  
- All reports will be limited to historical sales data.

### Ticket-Based Support
- Customer support will be handled exclusively through a ticket system.  
- There will be no built-in chatbot or live chat functionality.

### Manual Content Moderation
- All user-generated product reviews will require manual approval by an administrator before they are published on the website.

### Limited Scalability
- The current technical infrastructure is designed for a moderate volume of traffic.  
- The system may require a hosting upgrade to handle significant traffic surges during major sales events.

---

## 🧩 Project Proposal Statement

This project proposes a **web-based ordering system** for Esty Scents to enhance its online presence, operational efficiency, and customer experience.  

The solution aims to:
- Improve customer satisfaction  
- Simplify administrative processes  
- Boost sales through digital transformation  

All modifications during development will be reviewed and approved by stakeholders to ensure alignment with business goals.

---

## 🎯 Project Goals

- Launch a user-friendly, visually appealing online store  
- Enable 24/7 customer access and ordering  
- Streamline order processing and inventory management  
- Enhance communication and customer service  
- Increase online engagement and brand visibility  

---

## 📈 Success Metrics

| Metric | Target |
|--------|---------|
| Online Orders | 60% of total within 3 months |
| Inventory Errors | Reduced by 50% |
| Cart Abandonment | Reduced by 30% |
| Ticket Resolution | Within 24 hours |
| Online Sales Growth | +20% in 6 months |

---

## 🧑‍💻 Developers

**Developed by:**  
**University of Caloocan City**  
**Bachelor of Science in Computer Science Students**

| Name | Role | Responsibilities |
|------|------|------------------|
| **Ronan Aleck Gatmaitan** | Lead Developer / Backend | System architecture, PHP backend, database integration (MySQL), and API connections (PayMongo, Twilio). |
| **Alberto Magno Rili** | Frontend Developer / UI Designer | Website layout, responsive design, CSS framework implementation, and UX/UI improvements. |
| **Edgardo Sunga JR.** | Project Analyst / QA Tester | Documentation, testing, bug tracking, and feature validation for both admin and user portals. |

---

## 🧩 Installation & Setup Guide

### **Requirements**
- PHP 8.0+  
- MySQL 5.7+  
- XAMPP / WAMP / Laragon  
- Web browser (Chrome / Edge)  

### **Steps**
1. Clone or download this repository.  
2. Import the provided SQL file into your MySQL database.  
3. Update your `config.php` file with your database credentials.  
4. Run the project via `localhost/estyscents` on your local server.  
5. Access the admin panel via `/admin` (default login credentials will be provided).  

---

## 📬 Contact

**Company:** Esty Scents  
**Address:** ***************
**Email:** [info@estyscents.com](mailto:info@estyscents.com) *(placeholder)*  
**Phone:** +63 *********  

---

© 2025 **Esty Scents**. All Rights Reserved.
