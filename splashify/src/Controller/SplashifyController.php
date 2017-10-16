<?php
namespace Drupal\splashify\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\splashify\Entity\SplashifyEntity;
use Drupal\splashify\Entity\SplashifyGroupEntity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\node\Entity\Node;

/**
 * A splashify controller. Used in redirect-mode.
 */
class SplashifyController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content($id) {
    $entity = SplashifyEntity::load($id);

    if (empty($entity)) {
      throw new NotFoundHttpException();
    }

    $content = $entity->getContent();
    $group_id = $entity->getGroupId();
    $group = SplashifyGroupEntity::load($group_id);

    $splash_mode = $group->getSplashMode();

    // Render plain html or via site template.
    switch ($splash_mode) {
      case 'template':
        return [
          '#type' => 'markup',
          '#markup' => $content,
        ];

      case 'plain_text':

	$query = \Drupal::entityQuery('node')
		->condition('type', 'lightbox_content_type')
		->condition('status', 1)
		->sort('created' , 'DESC')
		->range(0, 1);

	$ids = $query->execute();

	foreach ($ids as $id) {
		$node = Node::load($id);
    $title = $node->get('title')->value;
    $node_content = $node->get('body')->value;
    $button = $node->get('field_button_text')->value;
    $bgentity = $node->field_background_image->entity;
    if ($bgentity) {
      $img_src= file_create_url($bgentity->getFileUri());
    }

    echo "
      <style>
        .cboxIframe html {
          overflow: hidden;
        }

        .cboxIframe body {
          margin: 0;
        }

        .lightbox-container {
          margin: 0 auto;
          padding: 25px;
          background-color: #0278ba;
        }

        @media only screen and (min-width: 500px) {
          .lightbox-container {
            margin: 40px;
          }
        }

        .lightbox-container .lightbox-content {
          margin: 0 auto;
          padding: 25px;
          border: 2px solid #fff;
        }

        @media only screen and (min-width: 600px) {
          .lightbox-container .lightbox-content {
            padding: 35px;
          }
        }

        .lightbox-container .lightbox-content .title {
          margin-bottom: 15px;
          color: #fff;
          font-family: 'Open Sans', sans-serif;
          line-height: 1.3em;
          text-align: center;
        }

        .lightbox-container .lightbox-content .body {
          margin-bottom: 30px;
          color: #fff;
          font-family: 'Open Sans', sans-serif;
          font-size: 1em;
          line-height: 1.65em;
          text-align: center;
        }

        .lightbox-container .lightbox-content .body p {
          margin: 0;
        }

        .lightbox-container .lightbox-content .donate-button {
          position: relative;
          display: block;
          max-width: 275px;
          margin: 0 auto;
          width: 100%;
          height: auto;
          padding: 25px 30px;
          outline: none;
          border: 0;
          border-radius: 50px;
          background-color: #d95929;
          color: #fff;
          font-size: 20px;
          text-transform: uppercase;
          line-height: 1;
          letter-spacing: 1px;
          transition: all 0.3s ease-in-out;
          z-index: 0;
          font-family: 'Open Sans', sans-serif;
          font-weight: bold;
          cursor: pointer;
        }

        .lightbox-container .lightbox-content .donate-button:hover {
          background-color: #F06000;
        }
      </style>";

    if ($bgentity) {
      echo "
        <style>
          .lightbox-background-image {
            display: table;
            height: 100%;
            max-height: 585px;
            margin: 0;
            padding: 25px;
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
          }

          @media only screen and (min-width: 500px) {
            .lightbox-background-image {
              max-height: 385px;
              margin: 40px;
            }
          }

          @media only screen and (min-width: 700px) {
            .lightbox-background-image {
              max-height: 490px;
            }
          }

          .lightbox-background-image .lightbox-container {
            display: table-cell;
            max-height: 525px;
            margin: 0 auto;
            padding: 25px;
            border: 2px solid #fff;
            background-color: transparent;
          }

          @media only screen and (min-width: 600px) {
            .lightbox-background-image .lightbox-container {
              padding: 35px;
            }
          }

          .lightbox-background-image .lightbox-container .lightbox-content {
            width: 100%;
            margin: 0 auto;
            margin-right: 0;
            padding: 0;
            border: 0;
          }

          @media only screen and (min-width: 700px) {
            .lightbox-background-image .lightbox-container .lightbox-content {
              width: 50%;
              margin-right: 0;
            }
          }

          .lightbox-background-image .lightbox-container .lightbox-content .title,
          .lightbox-background-image .lightbox-container .lightbox-content .body {
            text-align: left;
          }

          .lightbox-background-image .lightbox-container .lightbox-content .donate-button {
            display: inline-block;
            max-width: none;
          }
        </style>";
      echo "
        <div class='lightbox-background-image' style='background-image: linear-gradient(to right, rgba(2,97,158,0) 0%,rgba(1, 38, 62, 0.95) 100%), url($img_src);'>
          <div class='lightbox-container'>
            <div class='lightbox-content'>
              <h1 class='title'>$title</h1>
              <div class='body'>$node_content</div>
              <button class='round-button donate-button' onclick='window.location.href='#'>$button</button>
            </div>
          </div>
        </div>";
    } else {
      echo "
        <style>
          @media only screen and (min-width: 600px) {
            .lightbox-container .lightbox-content .title {
              font-size: 3em;
            }

            .lightbox-container .lightbox-content .body {
              font-size: 1.5em;
            }
          }
        </style>";
      echo "
        <div class='lightbox-container'>
          <div class='lightbox-content'>
            <h1 class='title'>$title</h1>
            <div class='body'>$node_content</div>
            <button class='round-button donate-button' onclick='window.location.href='#'>$button</button>
          </div>
        </div>";
    }
	}
	exit();

      default:
        throw new NotFoundHttpException();

    }
  }

}
