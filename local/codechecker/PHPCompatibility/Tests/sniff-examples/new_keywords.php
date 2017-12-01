<?php

$dir = __DIR__;

try {
    // something
} catch (Exception $e) {
    // something else
} finally {
    // finally something
}

class Talker {
    use A, B {
        B::smallTalk insteadof A;
        A::bigTalk insteadof B;
    }
}

namespace Foobar;

$namespace = __NAMESPACE__;

trait FoobarTrait {
    public function foobar() {
        $name = __TRAIT__;
    }
}

function gen_one_to_three() {
    for ($i = 1; $i <= 3; $i++) {
        // Note that $i is preserved between yields.
        yield $i;
    }
}

const TEST = 'Hello';

class testing {
    const TEST = 'Hello';
    const ok = 'a';

    public function something() {
        const TEST = 'This is not a class constant';
    }
}

interface testing {
    const TEST = 'Hello';
    const ok = 'a';

    public function something() {
        const TEST = 'This is not an interface constant';
    }
}

$a = new class {
    const TEST = 'Hello';
    const ok = 'a';

    public function something() {
        const TEST = 'This is not a class constant';
    }
}


function myTest(callable $callableMethod) {}

goto end;

end:
echo 'something';


__halt_compiler();

bla();
const ok = 'a';
