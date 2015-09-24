<?php
require_once('MessageInterface.php');

class Command implements MessageInterface
{
  protected $nodes;

  protected $conditions;
  protected $actions;
  protected $receivers;

  public function mark(BaseNode $node)
  {
    if (!$this->isMarked($node)) $this->nodes->attach($node);
  }

  public function isMarked(BaseNode $node)
  {
    return $this->nodes->contains($node);
  }

  public function __construct(array $receivers, array $conditions, array $actions)
  {
    $this->nodes = new SplObjectStorage;
    $this->receivers = $receivers;
    $this->conditions = $conditions;
    $this->actions = $actions;
  }

  public function conditions()
  {
    return $this->conditions;
  }

  public function actions()
  {
    return $this->actions;
  }

  public function isReceiver(MessengerInterface $messenger)
  {
    return in_array($messenger->getNode()->getName(), $this->receivers);
  }

  public function triggerType()
  {
    return 'forward';
  }
}
