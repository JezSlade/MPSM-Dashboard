     <div class="customer-selection">
       <label for="customer-select" class="sr-only">Select Customer</label>
-      <div class="select-wrapper">
+      <div class="select-wrapper glassmorphic">
         <select id="customer-select" name="customer_code">
           <option value="">-- Select Customer --</option>
           <?php if (!empty($customers)): ?>
             <?php foreach ($customers as $cust):
