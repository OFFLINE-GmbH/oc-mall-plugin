# Shipping methods

`Shipping methods` are presented to a customer during checkout.  

You can add certain restrictions on the availability of a shipping method.
If during checkout only one method is available it will be automatically set. 
If multiple methods are available the customer can choose between them. 

Make sure that you've got a shipping method available for all possible cases so your
customers won't hit a dead end during checkout. 

## Country restrictions

You can restrict a shipping method to certain countries. If a restriction is set, the method
will be only available for shipping to one of the selected countries. If no countries are 
selected the method will be available worldwide.

## Cart total restrictions

You can restrict a method to only be available above or below a certain cart total.
This feature enables you to offer a "Free Shipping" method above a certain total.

Don't forget to set the availability limits for both shipping methods if 
you want to use this feature.
This way the correct shipping method will be set automatically during checkout since only one will be available.

| Method | Available below | Available above |
| ------ | --------------- | --------------- |
| Default shipping | 100 USD |              |   
| Free shipping    |         | 100 USD       |   

## Rates (Weight)

You can create multiple rates to increase or decrease the shipping method's cost depending
on the total weight of an order.

To use this feature make sure to add `weight` information to each product. Products
without a weight property will be calculated as 0 gramms.