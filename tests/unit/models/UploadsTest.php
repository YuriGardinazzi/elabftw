<?php declare(strict_types=1);
/**
 * @author Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2022 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */

namespace Elabftw\Models;

use Elabftw\Elabftw\CreateImmutableUpload;
use Elabftw\Elabftw\CreateUpload;
use Elabftw\Enums\Action;
use Elabftw\Enums\FileFromString;
use Elabftw\Enums\State;
use Elabftw\Enums\Storage;
use Elabftw\Exceptions\IllegalActionException;
use Elabftw\Exceptions\ImproperActionException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class UploadsTest extends \PHPUnit\Framework\TestCase
{
    private Items $Entity;

    protected function setUp(): void
    {
        $this->Entity = new Items(new Users(1, 1), 11);
    }

    public function testCreate(): void
    {
        $params = $this->createMock(CreateUpload::class);
        // this would be the real name of the file uploaded by user
        $params->method('getFilename')->willReturn('example.png');
        // and this corresponds to the temporary file created after upload
        $tmpFilePath = '/tmp/phpELABFTW';
        $params->method('getFilePath')->willReturn($tmpFilePath);
        $fs = Storage::MEMORY->getStorage()->getFs();
        // write our temporary file as if it was uploaded by a user
        $fs->createDirectory('tmp');
        // a txt file was failing the mime type, so use a png
        $fixturesFs = Storage::FIXTURES->getStorage()->getFs();
        $fs->write(basename($tmpFilePath), $fixturesFs->read('example.png'));
        // we use the same fs for source and storage because it's all in memory anyway
        $params->method('getSourceFs')->willReturn($fs);

        $Uploads = new Uploads($this->Entity);
        $Uploads->create($params);
    }

    // same as above, but this file will fail mime type detection
    public function testCreateMimeFail(): void
    {
        $params = $this->createMock(CreateUpload::class);
        // this would be the real name of the file uploaded by user
        $params->method('getFilename')->willReturn('example.txt');
        // and this corresponds to the temporary file created after upload
        $tmpFilePath = '/tmp/phpELABFTW';
        $params->method('getFilePath')->willReturn($tmpFilePath);
        $fs = Storage::MEMORY->getStorage()->getFs();
        // write our temporary file as if it was uploaded by a user
        $fs->createDirectory('tmp');
        $fs->write(basename($tmpFilePath), 'blah');
        // we use the same fs for source and storage because it's all in memory anyway
        $params->method('getSourceFs')->willReturn($fs);

        $Uploads = new Uploads($this->Entity);
        $Uploads->create($params);
    }

    public function testCreatePngFromString(): void
    {
        $dataUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAABhWlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9TpSIVByuIKGSoThZERcRJq1CECqFWaNXB5NIPoUlDkuLiKLgWHPxYrDq4OOvq4CoIgh8gjk5Oii5S4v+SQosYD4778e7e4+4dINRKTLPaRgFNt81UIi5msiti6BUhDKIX0wjJzDJmJSkJ3/F1jwBf72I8y//cn6NLzVkMCIjEM8wwbeJ14slN2+C8TxxhRVklPiceMemCxI9cVzx+41xwWeCZETOdmiOOEIuFFlZamBVNjXiCOKpqOuULGY9VzluctVKFNe7JXxjO6ctLXKc5gAQWsAgJIhRUsIESbMRo1UmxkKL9uI+/3/VL5FLItQFGjnmUoUF2/eB/8LtbKz8+5iWF40D7i+N8DAGhXaBedZzvY8epnwDBZ+BKb/rLNWDqk/RqU4seAd3bwMV1U1P2gMsdoO/JkE3ZlYI0hXweeD+jb8oCPbdA56rXW2Mfpw9AmrpK3gAHh8BwgbLXfN7d0drbv2ca/f0AoG1yuTjmrdUAAAAGYktHRAD/AP8A/6C9p5MAAAAJcEhZcwAALiMAAC4jAXilP3YAAAAHdElNRQfmCAEBGRl6rBV0AAAAD3RFWHRDb21tZW50AGVMYWJGVFfEIDydAAAAG0lEQVQI12NkYGD4X1tby8Cwf//+/8+ePfsPAD1lCWVCgcPRAAAAAElFTkSuQmCC';
        $id = $this->Entity->Uploads->postAction(Action::CreateFromString, array(
            'file_type' => FileFromString::Png->value,
            'real_name' => 'some.png',
            'content' => $dataUrl,
        ));
        $this->assertIsInt($id);
    }

    public function testCreatePngFromInvalidString(): void
    {
        $dataUrl = 'data:';
        $this->expectException(RuntimeException::class);
        $this->Entity->Uploads->postAction(Action::CreateFromString, array(
            'file_type' => FileFromString::Png->value,
            'real_name' => 'invalid.png',
            'content' => $dataUrl,
        ));
    }

    public function testCreateFromStringNoExtension(): void
    {
        $id = $this->Entity->Uploads->postAction(Action::CreateFromString, array(
            'file_type' => FileFromString::Mol->value,
            'real_name' => 'no_extension',
            'content' => 'molfilecontent',
        ));
        $this->Entity->Uploads->setId($id);
        $this->assertEquals('no_extension.mol', $this->Entity->Uploads->uploadData['real_name']);
    }

    public function testUploadingPhpFile(): void
    {
        $this->expectException(ImproperActionException::class);
        $this->Entity->Uploads->create(new CreateUpload('some.php', __FILE__));
    }

    public function testReadBinary(): void
    {
        $id = $this->Entity->Uploads->create(new CreateUpload('some-file.zip', dirname(__DIR__, 2) . '/_data/importable.zip'));
        $this->Entity->Uploads->setId($id);
        $this->assertInstanceOf(Response::class, $this->Entity->Uploads->readBinary());
    }

    public function testPatch(): void
    {
        $id = $this->Entity->Uploads->create(new CreateUpload('some-file.zip', dirname(__DIR__, 2) . '/_data/importable.zip'));
        $this->Entity->Uploads->setId($id);
        $this->Entity->Uploads->patch(Action::Archive, array());
        $this->Entity->Uploads->patch(Action::Update, array(
            'real_name' => 'new real name',
            'comment' => 'new file comment',
            'state' => (string) State::Deleted->value,
        ));
    }

    public function testGetPage(): void
    {
        $this->assertIsString($this->Entity->Uploads->getPage());
    }

    public function testEditAnImmutableFile(): void
    {
        $id = $this->Entity->Uploads->create(new CreateImmutableUpload('some-immutable.zip', dirname(__DIR__, 2) . '/_data/importable.zip'));
        $this->Entity->Uploads->setId($id);
        $this->expectException(IllegalActionException::class);
        $this->Entity->Uploads->patch(Action::Update, array('real_name' => 'new'));
    }

    public function testGetStorageFromLongname(): void
    {
        $Uploads = new Uploads($this->Entity);
        $id = $Uploads->create(new CreateUpload('example.png', dirname(__DIR__, 2) . '/_data/example.png'));
        $Uploads->setId($id);
        $this->assertEquals($Uploads->uploadData['storage'], $Uploads->getStorageFromLongname($Uploads->uploadData['long_name']));
    }

    public function testReplace(): void
    {
        $Uploads = new Uploads($this->Entity);
        $id = $Uploads->create(new CreateUpload('example.png', dirname(__DIR__, 2) . '/_data/example.png', 'some super duper comment'));
        $Uploads->setId($id);
        $upArrBefore = $Uploads->uploadData;

        $id = $Uploads->postAction(Action::Create, array('real_name' => 'example.png', 'filePath' => dirname(__DIR__, 2) . '/_data/example.png'));
        $this->assertIsInt($id);
        // make sure the old one is archived
        $this->assertEquals($Uploads->readOne()['state'], State::Archived->value);
        $Uploads->setId($id);
        // make sure the comment is the same
        $this->assertEquals($upArrBefore['comment'], $Uploads->uploadData['comment']);
    }

    public function testInvalidId(): void
    {
        $this->expectException(IllegalActionException::class);
        $this->Entity->Uploads->setId(0);
    }

    public function testReadAll(): void
    {
        $this->assertIsArray($this->Entity->Uploads->readAll());
        // same including archived uploads
        $this->Entity->Uploads->includeArchived = true;
        $this->assertIsArray($this->Entity->Uploads->readAll());
    }

    public function testDestroyAll(): void
    {
        $this->assertTrue($this->Entity->Uploads->destroyAll());
    }
}
