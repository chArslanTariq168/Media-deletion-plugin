# Media Deletion and Category Images Plugin

This plugin allows users to add images to post categories by selecting them from the media library or uploading new ones on the Add Category and Edit Category pages.

Additionally, the plugin provides information on which posts, pages, and custom post types are attached to a particular media item. It creates a custom column in the Media List View called "Attached Object," where you can view the attached objects.

When deleting a media item, the plugin checks whether it is attached to any posts, pages, or custom post types. If it is, the plugin will not allow the user to delete it until it is detached from those specific objects
 
## APIs

This plugin provides several APIs to work with media.

# Get Media Data
```bash
assignment/v1', '/get_media_data/
```

This API is a POST request that requires an attachment ID ("id" as a request param). It will return the following data:

```bash
"data": {
        "id": 2740,
        "date": "2023-02-16 08:07:05",
        "slug": "a18",
        "type": "image/png",
        "link": "http://localhost/wordpress/wp-content/uploads/2023/01/A18.png",
        "alt_text": "",
        "attached_object": [12,23,34,45]
        }
```

Delete media in another api this plugin provide .

# Delete Media
```bash
assignment/v1', '/delete_media/
```
This API is also a POST request that requires an attachment ID("id" as a request param). The API will check whether the media is attached to any object and return the IDs of those objects if it is. If the media item is not attached to anything, the plugin will delete it and return a success message.