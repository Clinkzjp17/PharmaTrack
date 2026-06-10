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


sidebar_regex = re.compile(r'<div class="sidebar">.*?(?=<div class="main-content">)', re.DOTALL)

for file in files_to_convert:
    if not os.path.exists(file):
        
        alt_file = file.replace('-', '_')
        if os.path.exists(alt_file):
            file = alt_file
        else:
            print(f"File {file} not found.")
            continue
            
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    content = re.sub(r'href="([^"]+)\.html"', r'href="\1.php"', content)

    if '<div class="sidebar">' in content:
        content = sidebar_regex.sub("<?php include 'includes/sidebar.php'; ?>\n\n", content)
    
    php_file = file.replace('.html', '.php').replace('_', '-')
    with open(php_file, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Converted {file} to {php_file}")
    
    try:
        os.remove(file)
        print(f"Deleted {file}")
    except Exception as e:
        print(f"Could not delete {file}: {e}")
