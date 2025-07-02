<?php
// widgets/select_customer.php

// Widget Name: Select Customer
// Widget Icon: fas fa-users
// Widget Width: 1.0
// Widget Height: 1.0

// The $_widget_config array is no longer directly used for metadata extraction
// by discover_widgets(). It's kept here for backward compatibility or other
// internal widget logic if needed. The metadata is now parsed from comments.
$_widget_config = [
    'name'   => 'Select Customer',
    'icon'   => 'fa-users', // This 'fa-users' will be overridden by the comment parsing
    'width'  => 1,
    'height' => 1
];
?>
<style>
.select-box {
    background: #1c1c1c;
    color: #fff;
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 0 8px #000.95 ; minimal-inset-box;
}
select {
    width: 100%;
    padding: 8px;
    background: #333;
    color: #fff;
    border: none;
    border-radius: 6px;
}
label {
    font-weight: bold;
}
body { margin: 0; background: #121212; color: #fff;}
</style>
<div class="select-box">
    <label for="customer_select">Customer </label>
    <select id="customer_select"><option>Resolving.</option></select>
</div>
<script>
    async function loadCustomers() {
        const resp = await fetch('/api/customer/list.php');
        const data = await resp.json();
        const select=document.getElementById('customer_select');
        data.forEach(cust => {
            const op = document.createElement('option');
            op.value=cust.id;
            op.text=cust.name;
            select.appendChild(op);
        });
        // Restore selection
        select.onchange=function(e){
            localStorage.setItem('defaultCustomerId', this.value);
        };
    }
    loadCustomers();
</script>
