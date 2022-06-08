<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Course;
use App\Entity\Lesson;
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Курс 1
        $course = new Course();
        $course->setCode('PHP_Basic');
        $course->setName('PHP базовый курс');
        $course->setDescription('Вы освоите язык программирования PHP с нуля, а полученные на курсе знания примените на практике и напишете полноценный проект — облачное хранилище файлов.');
        $manager->persist($course);

            // Уроки к курсу 1
            $lesson = new Lesson();
            $lesson->setName('Введение в PHP');
            $lesson->setCourse($course);
            $lesson->setBody('Принципы работы динамических сайтов; принципы работы веб-серверов; подготовка рабочей среды; Hello, world! [Практика]; базовые конструкции языка – дескрипторы, переменные, типы данных; версии языка и их различия на базовом уровне.');
            $lesson->setNumber(1);
            $manager->persist($lesson);

            $lesson = new Lesson();
            $lesson->setName('Условные блоки, ветвление функции');
            $lesson->setCourse($course);
            $lesson->setBody('Принципы ветвления, визуализация, блок-схемы; операторы if, if-else; оператор switch; тернарный оператор; реализация схем логики ветвления; функции, рекурсия; использование функций и рекурсии для решения задач; области видимости переменных.');
            $lesson->setNumber(2);
            $manager->persist($lesson);

            $lesson = new Lesson();
            $lesson->setName('Циклы и массивы');
            $lesson->setCourse($course);
            $lesson->setBody('Понятие цикла, типы циклов в PHP; While, do…while; For; бесконечный цикл и выход из шагов цикла; понятие массива, типы массивов в PHP; применение циклов для работы с массивами [Практика]; многомерные массивы; основные функции работы с массивами; применение функции для работы с массивами [Практика]; предопределенные массивы.');
            $lesson->setNumber(3);
            $manager->persist($lesson);

        // Курс 2
        $course = new Course();
        $course->setCode('Databases');
        $course->setName('Основы реляционных баз данных. MySQL');
        $course->setDescription('Познакомитесь с языком запросов SQL. Научитесь писать запросы, делать расчёты и работать с таблицами. Узнаете основные ограничения SQL. Поработаете с MySQL и познакомитесь с альтернативными базами данных: MongoDB, Redis, ElasticSearch и ClickHouse.');
        $manager->persist($course);

            // Уроки к курсу 2
            $lesson = new Lesson();
            $lesson->setName('Вебинар. Установка окружения. DDL-команды');
            $lesson->setCourse($course);
            $lesson->setBody('Типы баз данных. Основы реляционных баз данных. СУБД MySQL. Клиенты. Управление базами данных.');
            $lesson->setNumber(1);
            $manager->persist($lesson);

            $lesson = new Lesson();
            $lesson->setName('Видеоурок. Управление БД. Язык запросов SQL');
            $lesson->setCourse($course);
            $lesson->setBody('Введение в SQL. Типы данных. Индексы. CRUD-операции');
            $lesson->setNumber(2);
            $manager->persist($lesson);

            $lesson = new Lesson();
            $lesson->setName('Вебинар. Введение в проектирование БД');
            $lesson->setCourse($course);
            $lesson->setBody('Проектирование БД.');
            $lesson->setNumber(3);
            $manager->persist($lesson);

        // Курс 3
        $course = new Course();
        $course->setCode('NodeJS');
        $course->setName('Серверное программирование на JavaScript');
        $course->setDescription('Курс познакомит со средой Node.js и научит работать с её основными модулями. Вы узнаете, что такое Node.js, поймёте как эта среда устроена и каким образом JavaScript может запускаться вне браузера. За время курса мы реализуем проект, задействующий основные модули Node.js.');
        $manager->persist($course);

            // Уроки к курсу 3
            $lesson = new Lesson();
            $lesson->setName('Введение в Node.js. Управление зависимостями');
            $lesson->setCourse($course);
            $lesson->setBody('Cтуденты после урока будут понимать, что такое Node.js, зачем он нужен, будут уметь инициализировать проект, узнают, что такое npm, а также выведут Hello World в консоль двумя разными способами.');
            $lesson->setNumber(1);
            $manager->persist($lesson);

            $lesson = new Lesson();
            $lesson->setName('Цикл событий. События в Node.js');
            $lesson->setCourse($course);
            $lesson->setBody('Вы поймете как работает Node.js, что такое асинхронные операции и в каком порядке они выполняются. Зачем нужен стандартный модуль Events, какие функции он выполняет. Зачем, когда и как использовать “события”.');
            $lesson->setNumber(2);
            $manager->persist($lesson);

            $lesson = new Lesson();
            $lesson->setName('Работа с файловой системой. Класс Buffer. Модуль Streams');
            $lesson->setCourse($course);
            $lesson->setBody('Мы изучим, что такое кодировка файла, познакомимся со стандартным модулем Node.js для работы с файловой системой. Научится читать, преобразовывать и записывать данные. Узнаем, что такое Buffer в Node.js и где его можно встретить. Изучим, что такое потоки в Node.js, узнаем о 4-х типах потоков, познакомимся с потоковым чтением и записью данных.');
            $lesson->setNumber(3);
            $manager->persist($lesson);

        $manager->flush();
    }
}
