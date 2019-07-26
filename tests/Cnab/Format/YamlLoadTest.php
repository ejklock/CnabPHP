<?php

namespace Cnab\Tests\Format;

use Cnab\Format\Linha;
use Cnab\Format\YamlLoad;

define('CNAB_FIXTURE_PATH', dirname(__FILE__).'/../../fixtures/yaml');

class YamlLoadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException DomainException
     * @expectedExceptionMessage O campo codigo_banco colide com o campo tipo_registro
     */
    public function testEmiteExceptionEmCamposComColisao()
    {
        $yamlLoad = new YamlLoad(0);

        $fields = [
            'codigo_banco' => [
                'pos' => [1, 3],
            ],
            'tipo_registro' => [
                'pos' => [1, 4],
            ],
        ];

        $yamlLoad->validateCollision($fields);
    }

    public function testNaoEmiteExceptionEmCamposSemColisao()
    {
        $yamlLoad = new YamlLoad(0);

        $fields1 = [
            'codigo_banco' => [
                'pos' => [1, 3],
            ],
            'tipo_registro' => [
                'pos' => [4, 4],
            ],
        ];

        $fields2 = [
            'codigo_banco' => [
                'pos' => [1, 3],
            ],
        ];

        $this->assertTrue($yamlLoad->validateCollision($fields1));
        $this->assertTrue($yamlLoad->validateCollision($fields2));
    }

    /**
     * @expectedException DomainException
     */
    public function testEmiteExceptionEmArrayMalformado()
    {
        $array = [
            'generic' => [
                'codigo_banco' => [
                    'pos'     => [1, 3],
                    'picture' => '',
                ],
                'tipo_registro' => [
                    'pos'     => [4, 4],
                    'picture' => '',
                ],
            ],
            '033' => [
                'nome_empresa' => [
                    'pos'     => [40, 80],
                    'picture' => '',
                ],
                'numero_inscricao' => [
                    'pos'     => [79, 80],
                    'picture' => '',
                ],
            ],
        ];

        $yamlLoad = new YamlLoad(0);
        $yamlLoad->validateArray($array);
    }

    public function testNaoEmiteExceptionEmArrayValido()
    {
        $array = [
            'generic' => [
                'codigo_banco' => [
                    'pos'     => [1, 3],
                    'picture' => '',
                ],
                'tipo_registro' => [
                    'pos'     => [4, 4],
                    'picture' => '',
                ],
            ],
            '033' => [
                'nome_empresa' => [
                    'pos'     => [40, 80],
                    'picture' => '',
                ],
                'numero_inscricao' => [
                    'pos'     => [81, 81],
                    'picture' => '',
                ],
            ],
        ];

        $yamlLoad = new YamlLoad(0);
        $this->assertTrue($yamlLoad->validateArray($array));
    }

    public function testBuscaFormatoGenericoEEspecifico()
    {
        $yamlLoad = $this->getMockBuilder('\Cnab\Format\YamlLoad')
                         ->setMethods(['loadYaml'])
                         ->setConstructorArgs([33])
                         ->getMock();

        $testFormat = [
            'codigo_banco' => [
                'pos'     => [1, 3],
                'picture' => '9(3)',
            ],
        ];

        $yamlLoad->expects($this->at(0))
                 ->method('loadYaml')
                 ->with(
                    $this->equalTo($yamlLoad->formatPath.'/cnab240/generic/header_lote.yml')
                )
                ->will($this->returnValue($testFormat));

        $yamlLoad->expects($this->at(1))
                 ->method('loadYaml')
                 ->with(
                    $this->equalTo($yamlLoad->formatPath.'/cnab240/033/header_lote.yml')
                )
                ->will($this->returnValue($testFormat));

        $linha = new Linha();
        $yamlLoad->load($linha, 'cnab240', 'header_lote');
    }
}
