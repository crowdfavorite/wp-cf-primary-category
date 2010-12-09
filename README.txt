# CF Primary Category

Adds a post-meta box to the post edit screen for selecting the primary category on a post. Any hierarchical taxonomy that is selected for the post is added to the primary category dropdown when it is selected. That term can then be selected as the primary category for the post. The first term selected will be automatically selected as the primary taxonomy.

`cfprimecat_get_primary_category()` can be used to retrieve the primary category taxonomy object.

The plugin also supplies a carrington-core filter for content & general template selection under the `tax-{taxonomy-slug}-{term_slug}.php` format.

Requires CF Post Meta.