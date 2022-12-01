<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Course;

class CourseTest extends AbstractTest
{
    private $startingPath = '/courses';

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    public function getPath(): string
    {
        return $this->startingPath;
    }

    // GET/POST
    /**
     * @dataProvider urlProviderSuccessful
     * @param $url
     */
    public function testPageIsSuccessful($url): void
    {
        $client = self::getClient();
        $client->request('GET', $url);
        self::assertResponseIsSuccessful();

        $em = self::getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();
        self::assertNotEmpty($courses);

        foreach ($courses as $course) {
            self::getClient()->request('GET', $this->getPath() . '/' . $course->getId());
            $this->assertResponseOk();

            self::getClient()->request('GET', $this->getPath() . '/' . $course->getId() . '/edit');
            $this->assertResponseOk();

            self::getClient()->request('POST', $this->getPath() . '/new');
            $this->assertResponseOk();

            self::getClient()->request('POST', $this->getPath() . '/' . $course->getId() . '/edit');
            $this->assertResponseOk();
        }
    }

    public function urlProviderSuccessful()
    {
        yield [$this->getPath() . '/'];
        yield [$this->getPath() . '/new'];
    }

    // 404 error
    /**
     * @dataProvider urlProviderNotFound
     * @param $url
     */
    public function testPageIsNotFound($url): void
    {
        $client = self::getClient();
        $client->request('GET', $url);
        $this->assertResponseNotFound();
    }

    public function urlProviderNotFound()
    {
        yield ['/non'];
        yield [$this->getPath() . '/13'];
    }

    // Main page
    public function testCourseIndex(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPath() . '/');
        $this->assertResponseOk();

        $em = self::getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();
        self::assertNotEmpty($courses);
        $coursesCountFromBD = count($courses);

        $coursesCount = $crawler->filter('div.card')->count();

        self::assertEquals($coursesCountFromBD, $coursesCount);
    }

    // Course page
    public function testCourseShow(): void
    {
        $em = self::getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();
        self::assertNotEmpty($courses);

        foreach ($courses as $course) {
            $crawler = self::getClient()->request('GET', $this->getPath() . '/' . $course->getId());
            $this->assertResponseOk();

            $lessonsCount = $crawler->filter('ol > li')->count();
            $lessonsCountFromBD = count($course->getLessons());

            static::assertEquals($lessonsCountFromBD, $lessonsCount);
        }
    }

    // Course: add, redirect, count, delete
    public function testCourseNewAddValidFieldsAndDeleteCourse(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPath() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.c_new')->link();
        $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('c_add', [
            'course[code]' => 'Something',
            'course[name]' => 'Random name',
            'course[description]' => 'Random description',
        ]);

        self::assertTrue($client->getResponse()->isRedirect($this->getPath() . '/'));

        $crawler = $client->followRedirect();

        $coursesCount = $crawler->filter('div.card')->count();

        self::assertEquals(4, $coursesCount);

        $link = $crawler->filter('a.card-link')->last()->link();
        $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('c_delete');

        self::assertTrue($client->getResponse()->isRedirect($this->getPath() . '/'));

        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $coursesCount = $crawler->filter('div.card')->count();
        self::assertEquals(3, $coursesCount);
    }

    // Course, code isn't valid
    public function testCourseNewAddNotValidCode(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPath() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.c_new')->link();
        $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('c_add', [
            'course[code]' => '',
            'course[name]' => 'Random name',
            'course[description]' => 'Random description',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Поле не может быть пустым', $error->text());

        $crawler = $client->submitForm('c_add', [
            'course[code]' => 'LoremipsumdolorsitametconsecteturadipiscingelitInullamcorpernunceuesteuismodconsecteturCrasmaximussapienveltristiquefaucibusVivamusconsectetursitametligulasedcondimentumAeneanvestibulumexacsapieninterdumutfermentumnibhullamcorperDonecplaceratornareporttitor',
            'course[name]' => 'Random name',
            'course[description]' => 'Random description',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Максимальная длина символьного кода 255 символов', $error->text());
    }

    // Course, name isn't valid
    public function testCourseNewAddNotValidName(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPath() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.c_new')->link();
        $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('c_add', [
            'course[code]' => 'Randomcode',
            'course[name]' => '',
            'course[description]' => 'Random description',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Название курса не может быть пустым', $error->text());

        $crawler = $client->submitForm('c_add', [
            'course[code]' => 'Randomcode',
            'course[name]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ullamcorper nunc eu est euismod consectetur. Cras maximus sapien vel tristique faucibus. Vivamus consectetur sit amet ligula sed condimentum. Aenean vestibulum ex ac sapien interdum, ut fermentum nibh ullamcorper. Donec placerat ornare porttitor.',
            'course[description]' => 'Random description',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Максимальная длина названия 255 символов', $error->text());
    }

    // Course, description isn't valid
    public function testCourseNewAddNotValidDescription(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPath() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.c_new')->link();
        $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('c_add', [
            'course[code]' => 'Randomcode',
            'course[name]' => 'Random name',
            'course[description]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ullamcorper nunc eu est euismod consectetur. Cras maximus sapien vel tristique faucibus. Vivamus consectetur sit amet ligula sed condimentum. Aenean vestibulum ex ac sapien interdum, ut fermentum nibh ullamcorper. Donec placerat ornare porttitor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ullamcorper nunc eu est euismod consectetur. Cras maximus sapien vel tristique faucibus. Vivamus consectetur sit amet ligula sed condimentum. Aenean vestibulum ex ac sapien interdum, ut fermentum nibh ullamcorper. Donec placerat ornare porttitor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ullamcorper nunc eu est euismod consectetur. Cras maximus sapien vel tristique faucibus. Vivamus consectetur sit amet ligula sed condimentum. Aenean vestibulum ex ac sapien interdum, ut fermentum nibh ullamcorper. Donec placerat ornare porttitor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ullamcorper nunc eu est euismod consectetur. Cras maximus sapien vel tristique faucibus. Vivamus consectetur sit amet ligula sed condimentum.',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Максимальная длина описания 1000 символов', $error->text());
    }

    // Course, edit & redirect
    public function testCourseEditAndCheckFields(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPath() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.c_edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->selectButton('c_add')->form();

        $em = self::getEntityManager();
        $course = $em->getRepository(Course::class)->findOneBy(['code' => $form['course[code]']->getValue()]);

        $form['course[code]'] = 'Randomcode';
        $form['course[name]'] = 'Random name';
        $form['course[description]'] = 'Random description';

        $client->submit($form);

        self::assertTrue($client->getResponse()->isRedirect($this->getPath() . '/' . $course->getId()));

        $crawler = $client->followRedirect();
        $this->assertResponseOk();
    }
}