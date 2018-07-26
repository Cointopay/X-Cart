# Drupal Plugin for Cointopay

Cointopay.com crypto payment plugin for: **Xcart**

**Plugin is compatible with Xcart 5.3 version**

## Install

Please sign up for an account at <https://cointopay.com/Signup.jsp> Cointopay.com.

Note down the MerchantID, SecurityCode and Currency, information is located in the Account section. These pieces of information are mandatory to be able to connect the payment module to your Drupal.

### Via Xcart Module Upload

Installation of coin to pay plugin can be done by following steps below

1) Go to your Xcart installation directory
2) Unzip the coin to pay plugin 
3) Redirect to admin area of your website and from left menu Click on Store setup >> Cache Management  
4) Click on start button in front of Re-deploy the store option
5) Now click on Store setup >> payment methods
6) On payment methods page Click on Add payment method and search for coin to pay and click install button
7) After installation you will be redirected to Coin to pay settings page insert your merchant id, security code and select coin for user transactions
8) Make sure to activate the payment method
9) If you want to configure coin to pay after installation then click on Store setup >> payment methods.
10) Click on configure button in front of coin to pay from list of online methods.


### Testing

After login go to Store setup >> Payment methods and click on configure option below coin to pay method to configure coin to pay settings.

To test coin to pay do the following steps.
1) Go to home page url
2) Click on test product add add test product to your cart
3) Click on cart  from top right corner 
4) Inside cart click on checkout option 
5) Select delivery method and click on proceed to payment 
6) Select coin to pay from the list of payment methods 
7) Click on place order which will redirect you to coin to pay website
8) After payment you will be redirected to xcart
9) Go to domain/admin.php then click on Orders >> Order list to check the order.
