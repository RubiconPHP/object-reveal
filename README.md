# ObjectReveal

Simple wrapper that allow access to object's private and protected members.

### Requirements

PHP >= 5.4

### Getting Started


```
class User
{
    private $name;
    
    private function getName()
    {
        return $this->name;
    }
}

$revealed = new Rubicon\ObjectReveal\ObjectReveal(new User);
$revealed->name = 'John';
echo $revealed->getName(); // 'John'

```