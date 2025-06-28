# 🛒 Senior Project PHP API

This repository contains the complete PHP backend API for a mobile app that connects **customers** with **vendors** (small businesses). It enables product management, customer interaction, and order handling.

## 🌐 Technologies Used

- PHP (Core)
- MySQL (Database)
- RESTful API
- JSON Response

---

## 📌 Key Features

### 🧑‍💼 Vendor Features
- Add, edit, and delete products
- View customer feedback on products
- Manage incoming orders
- Change order statuses

### 🛍️ Customer Features
- View all vendors and products
- Add products to cart
- Submit orders
- Add/remove favorite stores
- Comment on products
- Edit personal profile

### 🛡️ Admin Features
- View/delete customer comments
- Manage vendor statuses
- Review all submitted products

---

## 📁 File Structure Overview

```bash
/Senior
│
├── config.php
│
├── add_product.php
├── edit_product_vendor.php
├── delete_product.php
├── get_all_Product.php
│
├── create_order.php
├── get_cart.php
├── delete_cart.php
│
├── add_favorite_store.php
├── get_favorite_store.php
├── remove_favorite_store.php
│
├── add_product_comment.php
├── get_all_comment_vendor.php
├── get_all_comment_admin.php
├── delete_comment_admin.php
│
├── edit_profile_customers.php
├── get_customers_profile.php
├── get_all_store_customer.php
├── get_all_vendors_status.php
├── change_status_order_vendor.php
