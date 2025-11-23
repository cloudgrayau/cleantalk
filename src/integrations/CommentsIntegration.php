<?php
namespace cloudgrayau\cleantalk\integrations;
use cloudgrayau\cleantalk\Cleantalk;

use Craft;
use yii\base\Event;

class CommentsIntegration {

  public function parse(): void {
    Event::on(\verbb\comments\elements\Comment::class, \verbb\comments\elements\Comment::EVENT_BEFORE_VALIDATE, function(\yii\base\ModelEvent $e){
      $comment = $e->sender;
      if ($comment->userId){
        $user = Craft::$app->getUser()->getIdentity();
        $params = [
          'name' => $user->fullName,
          'email' => $user->email
        ];
      } else {
        $params = [
          'name' => $comment->name,
          'email' => $comment->email
        ];
      }
      $params['message'] = $comment->getComment();
      if (!Cleantalk::$plugin->antiSpam->checkSpam($params)){
        $comment->addError('comment', 'Comment blocked due to spam.');
      }
    });
  }
  
}

?>