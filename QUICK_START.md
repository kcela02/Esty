# 🚀 Quick Start Guide - New Features

## ⚡ Quick Setup

### 1. Run Migrations
Visit these URLs to create database tables:
```
http://localhost/Esty-main/ESTY/migrations.php
http://localhost/Esty-main/ESTY/migrations_wishlist_compare.php
```

### 2. Test Features

#### 📦 Test Categories & Brands
1. Login as admin
2. Go to **Sidebar → Categories** or **Sidebar → Brands**
3. Click "Add Category/Brand"
4. Add sample categories: "Car Diffusers", "Room Sprays", "Helmet Deodorizers"
5. Add sample brands: "Esty Premium", "Esty Classic"

#### ⭐ Test Reviews & Ratings
1. Login as customer
2. Go to **index.php** or **products.php**
3. Click on any product
4. Scroll to "Write a Review" section
5. Select rating, add title and comment
6. Submit review
7. See average rating update on product cards

#### ❤️ Test Wishlist
1. Login as customer
2. Go to product details
3. Click "Add to Wishlist" button
4. Click ❤️ icon in navbar
5. View all wishlist items
6. Can add to cart from wishlist

#### 📊 Test Compare
1. Go to **products.php**
2. Add products to compare (up to 4)
3. Click 📊 "Compare Products" in navbar
4. View side-by-side comparison
5. Add compared products to cart

#### 🔍 Test Filters
1. Go to **products.php**
2. Use sidebar filters:
   - Search by name
   - Filter by category
   - Filter by brand
   - Set price range (e.g., 100-500)
   - Filter by minimum rating (e.g., 3+ stars)
   - Sort by: Price, Rating, Popularity, Newest
3. See results update

---

## 📱 Key Page URLs

| Feature | URL | Notes |
|---------|-----|-------|
| Featured Products | `/index.php` | Shows ratings & compare button |
| All Products | `/products.php` | Advanced filters & search |
| Product Details | `/product_details.php?id=1` | Reviews & wishlist |
| My Wishlist | `/wishlist.php` | Logged-in users only |
| Compare Products | `/compare.php` | Guest or logged-in |
| Admin Categories | `/admin/manage_categories.php` | Admin only |
| Admin Brands | `/admin/manage_brands.php` | Admin only |

---

## 🎯 Feature Flow Diagrams

### Customer Journey: From Browse to Review
```
index.php (Featured Products with Ratings)
    ↓
products.php (Filter & Search)
    ↓
product_details.php (View Details)
    ↓
Add to Wishlist / Add to Compare / Submit Review
    ↓
wishlist.php / compare.php / Reviews displayed
```

### Admin Journey: Setup Products
```
admin/manage_categories.php (Create Categories)
    ↓
admin/manage_brands.php (Create Brands)
    ↓
admin/products.php (Assign to Products)
    ↓
Products appear with category/brand badges
```

---

## 🔧 Configuration & Customization

### Change Max Compare Products
File: `process_compare.php` (Line ~20)
```php
if ($row['cnt'] >= 4) {  // Change 4 to desired number
    echo json_encode(['success' => false, 'message' => 'Maximum 4 products...']);
}
```

### Change Review Minimum Length
File: `process_review.php` (Line ~20)
```php
if (strlen($comment) < 20) {  // Change 20 to desired length
    echo json_encode(['success' => false, 'message' => 'Review must be...']);
}
```

### Change Star Colors
Files: `product_details.php`, `products.php`
```css
.stars { color: rgb(251, 191, 36); }  /* Gold stars */
/* Change RGB values to desired color */
```

---

## 🐛 Troubleshooting

### Issue: Reviews not submitting
**Solution:** 
1. Check user is logged in
2. Verify minimum comment length (20 chars)
3. Check database connection in `process_review.php`

### Issue: Wishlist button not working
**Solution:**
1. User must be logged in
2. Check `process_wishlist.php` for errors
3. Verify wishlists table exists

### Issue: Compare showing 0 products
**Solution:**
1. Check `process_compare.php` for errors
2. Try clearing session/cache
3. Verify compare_products table created

### Issue: Filters not working
**Solution:**
1. Verify categories/brands exist in database
2. Products must have category_id/brand_id assigned
3. Check `products.php` database query

---

## 📊 Database Schema Quick Reference

```sql
-- View categories
SELECT * FROM categories;

-- View brands  
SELECT * FROM brands;

-- View all product reviews
SELECT pr.*, u.username 
FROM product_reviews pr
JOIN users u ON pr.user_id = u.id
ORDER BY pr.created_at DESC;

-- View wishlist for user
SELECT p.* FROM wishlists w
JOIN products p ON w.product_id = p.id
WHERE w.user_id = 1;

-- View compare list
SELECT p.* FROM compare_products cp
JOIN products p ON cp.product_id = p.id
WHERE cp.user_id = 1;
```

---

## 🎨 Styling Notes

- **Gold Color**: `rgb(201, 166, 70)` - Primary accent
- **Pink Color**: `rgb(231, 84, 128)` - Secondary accent
- **Star Color**: `rgb(251, 191, 36)` - Ratings
- **Card Shadow**: `0 8px 20px rgba(0,0,0,0.15)` - Hover effect

---

## ✅ Testing Checklist

- [ ] Add category and verify on products page
- [ ] Add brand and verify filtering works
- [ ] Submit review and see rating update
- [ ] Add product to wishlist
- [ ] View wishlist page
- [ ] Add products to compare (test 4-product limit)
- [ ] View comparison table
- [ ] Test price range filter
- [ ] Test rating filter
- [ ] Test search functionality
- [ ] Test sort options
- [ ] Verify guest can compare
- [ ] Verify guest cannot see wishlist link
- [ ] Test add to cart from wishlist
- [ ] Test add to cart from compare

---

## 📞 Support

For questions or issues:
1. Check database migrations ran successfully
2. Verify all files are created in correct locations
3. Check browser console for JavaScript errors
4. Check server error logs
5. Verify user is logged in for user-specific features

**Happy Selling! 🎁**
