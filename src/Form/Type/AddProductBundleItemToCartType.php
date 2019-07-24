<?php

declare(strict_types=1);

namespace BitBag\SyliusProductBundlePlugin\Form\Type;

use BitBag\SyliusProductBundlePlugin\Command\AddProductBundleItemToCartCommand;
use BitBag\SyliusProductBundlePlugin\Entity\ProductInterface;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantChoiceType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantMatchType;
use Sylius\Component\Core\Model\Product;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

final class AddProductBundleItemToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => 'sylius.ui.quantity',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var AddProductBundleItemToCartCommand $data */
            $data = $event->getData();

            $form = $event->getForm();

            /** @var ProductInterface $product */
            $product = $data->getProductVariant()->getProduct();

            if ($product->hasVariants() && !$product->isSimple()) {
                $type =
                    Product::VARIANT_SELECTION_CHOICE === $product->getVariantSelectionMethod()
                        ? ProductVariantChoiceType::class
                        : ProductVariantMatchType::class
                ;

                $form->add('productVariant', $type, [
                    'product' => $product,
                ]);
            }
        });

        if (isset($options['product']) && $options['product']->hasVariants() && !$options['product']->isSimple()) {
            $type =
                Product::VARIANT_SELECTION_CHOICE === $options['product']->getVariantSelectionMethod()
                    ? ProductVariantChoiceType::class
                    : ProductVariantMatchType::class
            ;

            $builder->add('productVariant', $type, [
                'product' => $options['product'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'product',
            ])
            ->setAllowedTypes('product', ProductInterface::class)
            ->setDefaults([
                'data_class' => AddProductBundleItemToCartCommand::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'bitbag_sylius_product_bundle_plugin_add_product_bundle_item_to_cart';
    }
}
