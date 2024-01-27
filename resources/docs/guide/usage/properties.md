# Properties

Properties are used to describe a [Product or Variant](./products.md) in your store.

Properties serve two purposes:

1. They can be used to describe a product. A user can filter on different properties and find matching products.
1. Different property values can be combined to define a [Variant](./products.md). 

## Defining properties

Properties can be defined in the backend under `Catalogue > Properties`. 

### Property groups

Every property has to belong to a property group. Common property groups are `Size`, `Dimensions` or `Technical 
specs`. You can of course define as many groups as your use-case demands.

Every group needs to be given a `Name` (used in the backend). The `Display name`
(optional name to use in the frontend) and `Description` attributes are optional.

If you manage a high number of products you can use the `Name` and `Display Name` properties to give your groups 
descriptive names with more context in the backend (`Bike Size`, `Shoe Size`, `Package Size`) while
displaying a more generic name in the frontend (`Size` for everything, context is
given by the currently viewed category).    

::: tip
Make sure to name your groups only as specific as needed. Giving then generic names will
make it easier to re-use them among different products.
:::

### Properties

Each `Property` belongs to one or multiple `Property Groups`.

A `Property` has a descriptive `Name`, a `Type` and an optional `Unit`.

The table below shows some common `Properties`.

| Name        | Type          | Unit | Options            |
| ----------- | ------------- | ---- | ------------------ |
| Size        | Dropdown      |      |  S, M, L, XL       |
| Color       | Color         |      |                    |
| Width       | Number        | mm   |                    |
| Height      | Number        | mm   |                    |
| Material    | Textarea      |      |                    |

#### Use for Variants

If a `Property` will be used to describe the different [Variants of a Product](./products.md) this option
should be enabled. A perfect example is the `Size` property where a Product is available as differently
sized Variants. The `Material` property will be the same for all sizes
so it should *not* be used to describe Variants.

#### Filter type

To make products filterable by a specific property, the `Filter type` option can be enabled.

The table below shows some common `Filter type` setups.

| Property    | Type           |
| ----------- | -------------- |
| Size        | Set            |
| Color       | Set            |
| Width       | Range          |
| Height      | Range          |
| Ingredients | Without filter |

A `Set` filter will display all available Property values. The user can select one or many of these values to filter 
products.

A `Range` filter will display a slider control. The user can set a specific range to filter matching products. 
