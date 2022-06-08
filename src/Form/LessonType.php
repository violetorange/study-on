<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Regex;

class LessonType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Максимальная длина названия {{ limit }} символов',
                    ]),
                    new NotBlank([
                        'message' => 'Название не может быть пустым',
                    ]),
                ],
                'required' => false,
            ])
            ->add('body', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Содержание урока не может быть пустым',
                    ]),
                ],
            ])
            ->add('number', NumberType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d{1,}/',
                        'message' => 'Номер урока должен быть цифрой',
                    ]),
                    new NotBlank([
                        'message' => 'Номер урока не может быть пустым',
                    ]),
                ],
                'invalid_message' => 'Номер урока должен быть цифрой',
                'required' => false,
            ])
            ->add('course', HiddenType::class)
        ;

        $builder->get('course')
            ->addModelTransformer(new CallbackTransformer(
                function (Course $course) {
                    return $course->getId();
                },
                function (int $courseId) {
                    return $this->entityManager->getRepository(Course::class)->find($courseId);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}
