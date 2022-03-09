# Magento Media IO
Update and export product images from your Magento's 1.x store.

# How to use
Firstly you need to merge the module's files with your magento's root directory to install it. After that, the submenu "Product Images" should be already avaiable in 
the menu "Catalog":

# Export images
To get all images from your products go to `Catalog -> Product Images -> Export` and click the "Export Images" button. After that, a .XLSX file containg your products and it's images will be automatically downloaded.

You can also **filter products by categories**, selecting the desired category in the field "Filter by category". Doing this, only the products of the selected category will be exported.

# Update images
To update products images go to `Catalog -> Product Images -> Import`, select the file .CSV containg the images that will be imported to each SKU (product).

The CSV file must have the following columns (even if it's empty):
|SKU|Image|Small Image|Thumbnail| Media Gallery |
| --- | --- | --- | --- | --- |
|product-sku|https://link-to-image/|https://link-to-image/|https://link-to-image/|https://link-to-image/;https://link-to-image-2/||

Where:

- `SKU` = Product's SKU
- `Image` = Product's Base Image
- `Small Image` = Product's Small Image 
- `Thumbnail` = Product's Thumbnail
- `Media Gallery` = Images that are displayed when the product is opened in the store (extra images). To add more than 1 image you must separete each image with `;`

You can also assign images that are inside the folder `media/import`, in your magento's root folder, to products. For that, you would need a CSV file like this:
|SKU|Image|Small Image|Thumbnail| Media Gallery |
| --- | --- | --- | --- | --- |
|product-sku|image-1.jpg|image-2.jpg|image-3.jpg|image-4.jpg;image-5.jpg|

Where `image-1.jpg`, `image-2.jpg`, `image-3.jpg`, `image-4.jpg` and `image-5.jpg` are images inside `media/import`.

Obs.: the folder `media/import` isn't created by magento, you will need to create it if not exists.
