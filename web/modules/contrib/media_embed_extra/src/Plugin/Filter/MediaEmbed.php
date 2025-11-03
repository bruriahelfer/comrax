<?php

namespace Drupal\media_embed_extra\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\Plugin\FilterInterface;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\Filter\MediaEmbed as CoreMediaEmbed;

/**
 * Provides a filter to embed media items using a custom tag.
 *
 * @internal
 */
#[Filter(
  id: "media_embed",
  title: new TranslatableMarkup("Embed media"),
  description: new TranslatableMarkup("Embeds media items using a custom tag, <code>&lt;drupal-media&gt;</code>. If used in conjunction with the 'Align/Caption' filters, make sure this filter is configured to run after them."),
  type: FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
  weight: 100,
  settings: [
    "default_view_mode" => "default",
    "allowed_view_modes" => [],
    "allowed_media_types" => [],
  ],
)]
class MediaEmbed extends CoreMediaEmbed {

  /**
   * {@inheritdoc}
   */
  protected function applyPerEmbedMediaOverrides(\DOMElement $node, MediaInterface $media) {
    parent::applyPerEmbedMediaOverrides($node, $media);
    if ($image_field = $this->getMediaImageSourceField($media)) {

      // Check if height and width properties have been provided.
      $height = (int) $node->getAttribute('data-height');
      $width = (int) $node->getAttribute('data-width');

      // Resize proportionally if only one value was provided.
      if (empty($width) && !empty($height) ||
          empty($height) && !empty($width)) {
        if (empty($width)) {
          $width = $height * $media->{$image_field}->width / $media->{$image_field}->height;
        }
        else {
          $height = $width * $media->{$image_field}->height / $media->{$image_field}->width;
        }
      }
      if (!empty($height)) {
        $media->{$image_field}->height = $height;
      }
      if (!empty($width)) {
        $media->{$image_field}->width = $width;
      }
    }
  }

}
