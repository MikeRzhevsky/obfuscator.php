<?php


use \PHPUnit\Framework\TestCase;
use MikeR\App\Obfuscator;

final class ObfuscatorTest extends TestCase
{
    public function testObfuscatorRun()
    {
        $path_input_script  = __DIR__.'/src/public/test.dump.sql';
        $path_out_script    = __DIR__.'/src/public/output.sql';
        $path_config        = __DIR__.'/src/config/obfuscator.config.php';
        $obfuckator = new Obfuscator($path_input_script,$path_out_script,$path_config);
        $this->assertEquals(
            'INSERT INTO `user` ( `email`, `name`, `surname`, `phone`)

VALUES

(DFKCF[^S9ZB,D��Ͽ޵ò���һѨ�U,D��ÿ׵ɳ˩ˤѻ�^,\'79097503375\')',
            $obfuckator->run()
        );

    }
}
