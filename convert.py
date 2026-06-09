import os
import re

files_to_convert = [
    'admin-login.html',
    'dashboard.html',
    'lowstock.html',
    'products.html',
    'reservation.html',
    'stocks.html',
    'user-login.html',
    'users.html',
    'expiry.html'
]

# We need to capture the sidebar div entirely. 
# It starts with <div class="sidebar"> and ends before <div class="main-content">
sidebar_regex = re.compile(r'<div class="sidebar">.*?(?=<div class="main-content">)', re.DOTALL)

for file in files_to_convert:
    if not os.path.exists(file):
        # The user said admin_login.html, check if it exists with underscore
        alt_file = file.replace('-', '_')
        if os.path.exists(alt_file):
            file = alt_file
        else:
            print(f"File {file} not found.")
            continue
            
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Replace links (e.g., href="dashboard.html" -> href="dashboard.php")
    content = re.sub(r'href="([^"]+)\.html"', r'href="\1.php"', content)

    # 2. Extract and replace sidebar if it exists
    if '<div class="sidebar">' in content:
        content = sidebar_regex.sub("<?php include 'includes/sidebar.php'; ?>\n\n", content)
    
    # 3. Write out the new PHP file
    php_file = file.replace('.html', '.php').replace('_', '-')
    with open(php_file, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Converted {file} to {php_file}")
    
    # Optionally remove the old HTML file to keep the folder clean
    try:
        os.remove(file)
        print(f"Deleted {file}")
    except Exception as e:
        print(f"Could not delete {file}: {e}")

