<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Course;
use App\Entity\Lesson;

class LessonTest extends AbstractTest
{
    private $startingPathCourse = '/courses';

    private $startingPathLesson = '/lessons';

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    public function getPathCourse(): string
    {
        return $this->startingPathCourse;
    }

    public function getPathLesson(): string
    {
        return $this->startingPathLesson;
    }

    // Lessons http-statuses of courses
    public function testPageIsSuccessful(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        $courseLinks = $crawler->filter('a.card-link')->links();
        foreach ($courseLinks as $courseLink) {
            $crawler = $client->click($courseLink);
            $this->assertResponseOk();

            $lessonLinks = $crawler->filter('a.card-link')->links();
            foreach ($lessonLinks as $lessonLink) {
                $crawler = $client->click($lessonLink);
                self::assertResponseIsSuccessful();
            }
        }
    }

    // Lesson doesn't exist
    public function testPageIsNotFound(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathLesson() . '/-1');
        $this->assertResponseNotFound();
    }

    // Lesson: add, delete, redirect
    public function testLessonNewAddValidFieldsAndDeleteCourse(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.l_new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->selectButton('l_add')->form();
        // Изменяем поля в форме
        $form['lesson[name]'] = 'Random lesson';
        $form['lesson[body]'] = 'Random text';
        $form['lesson[number]'] = '1';

        $em = static::getEntityManager();
        $course = $em->getRepository(Course::class)->findOneBy(['id' => $form['lesson[course]']->getValue()]);
        self::assertNotEmpty($course);

        $client->submit($form);

        self::assertTrue($client->getResponse()->isRedirect($this->getPathCourse() . '/' . $course->getId()));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $link = $crawler->filter('ol > li > a')->first()->link();
        $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('l_delete');

        self::assertTrue($client->getResponse()->isRedirect($this->getPathCourse() . '/' . $course->getId()));

        $crawler = $client->followRedirect();
        $this->assertResponseOk();
    }

    // Lesson, name isn't valid
    public function testLessonNewAddNotValidName(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.l_new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('l_add', [
            'lesson[name]' => '',
            'lesson[body]' => 'Random text',
            'lesson[number]' => '20',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Название не может быть пустым', $error->text());
    }

    // Lesson, body isn't valid
    public function testLessonNewAddNotValidbody(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.l_new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('l_add', [
            'lesson[name]' => 'Random name',
            'lesson[body]' => '',
            'lesson[number]' => '20',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Содержание урока не может быть пустым', $error->text());
    }

    // Lesson, number isn't valid
    public function testLessonNewAddNotValidNumber(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.l_new')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('l_add', [
            'lesson[name]' => 'Random name',
            'lesson[body]' => 'Random text',
            'lesson[number]' => '',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Номер урока не может быть пустым', $error->text());

        $crawler = $client->submitForm('l_add', [
            'lesson[name]' => 'Random name',
            'lesson[body]' => 'Random text',
            'lesson[number]' => 'nfgh4(',
        ]);

        $error = $crawler->filter('div.invalid-feedback')->first();
        self::assertEquals('Номер урока должен быть цифрой', $error->text());
    }

    // Lesson, edit & redirect
    public function testLessonEditAndCheckFields(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', $this->getPathCourse() . '/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('ol > li > a')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.l_edit')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->selectButton('l_add')->form();

        $em = self::getEntityManager();
        $lesson = $em->getRepository(Lesson::class)->findOneBy([
            'number' => $form['lesson[number]']->getValue(),
            'course' => $form['lesson[course]']->getValue(),
        ]);

        $form['lesson[name]'] = 'Rand name';
        $form['lesson[body]'] = 'Rand text';

        $client->submit($form);

        self::assertTrue($client->getResponse()->isRedirect($this->getPathLesson() . '/' . $lesson->getId()));

        $crawler = $client->followRedirect();
        $this->assertResponseOk();
    }
}
