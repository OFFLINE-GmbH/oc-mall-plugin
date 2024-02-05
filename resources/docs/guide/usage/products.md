# Products and Variants

A Product is the central entity in `oc-mall`. 

A Product has predefined properties like a price, a description or a stock value. It belongs to a category and brand, it
 provides downloads or can be made customizable by the end-user with custom fields. For simple goods this 
 will be all you need. 
 
 If you sell goods with more complex properties, you can make use of Variants. Variants belong to a parent Product. 
 They inherit every property of this Product except the one that are explicitly changed.
 
 ## Examples
 
 The following examples help to illustrate the concept of Products and Variants.
 
 | Type     | Good                                | Can be bought directly | 
 | ----     | ----------------------------------- | -----------------------|
 | Product  | Your band's new record on CD        | Yes                    |
 |          |                                     |                        |
 | Product  | Your band's new record on Vinyl     | Yes                    |
 |          |                                     |                        |
 | Product  | Your band's new fan shirt           | No                     | 
 | Variant  | Your band's new fan shirt in Size S | Yes                    |
 | Variant  | Your band's new fan shirt in Size M | Yes                    |
 | Variant  | Your band's new fan shirt in Size L | Yes                    |
  
The music record on CD and Vinyl is handled as two different products. You cannot buy them in
different colors or sizes. Each product has its own stock.

The fan shirt is available in three different sizes. The Variants share all the same properties as the Product has, 
except for their size. Each Variant has its own stock value. The stock value of the Product is the sum of all 
Variant's stock values.

If a Product has Variants you cannot buy the Product itself, only its Variants.

## Inventory Management Method

The Inventory Management Method defines if your Product is a "simple Product" or is sold as different Variants.

## Group by property

This option allows you to define a property that makes your Variants unique.

Let's say you have a T-Shirt that is available in the sizes S, M and L and the colors blue and red.
In this case you want to group by color since this makes the Variants unique. 

## Properties

The [Properties](./properties.md) a Product or Variant has are defined by the [Category](./categories.md) they belong to.

Define your Property groups and Categories before you create any products.

