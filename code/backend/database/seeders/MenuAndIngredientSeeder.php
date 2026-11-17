<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\DishIngredient;
use App\Models\ComboRule;

class MenuAndIngredientSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::first();

        // 1. Tạo nguyên liệu mẫu
        $ingredientsData = [
            ['name' => 'Thịt bò tươi', 'current_stock' => 50.0, 'unit' => 'kg', 'minimum_threshold' => 5.0],
            ['name' => 'Bánh phở tươi', 'current_stock' => 40.0, 'unit' => 'kg', 'minimum_threshold' => 4.0],
            ['name' => 'Thịt heo ba chỉ', 'current_stock' => 30.0, 'unit' => 'kg', 'minimum_threshold' => 3.0],
            ['name' => 'Gà ta thả vườn', 'current_stock' => 25.0, 'unit' => 'kg', 'minimum_threshold' => 2.0],
            ['name' => 'Cơm tấm / Gạo thơm', 'current_stock' => 100.0, 'unit' => 'kg', 'minimum_threshold' => 10.0],
            ['name' => 'Trà xanh / Trà bắc', 'current_stock' => 10.0, 'unit' => 'kg', 'minimum_threshold' => 1.0],
            ['name' => 'Trứng gà', 'current_stock' => 200.0, 'unit' => 'quả', 'minimum_threshold' => 20.0],
            ['name' => 'Đá viên tinh khiết', 'current_stock' => 100.0, 'unit' => 'túi', 'minimum_threshold' => 5.0],
        ];

        $ingredientMap = [];
        foreach ($ingredientsData as $ing) {
            $created = Ingredient::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $ing['name']],
                $ing
            );
            $ingredientMap[$ing['name']] = $created;
        }

        // 2. Tạo danh mục và món ăn
        $catalog = [
            'Món chính' => [
                ['name' => 'Phở Bò Tái Nạm', 'price' => 60000, 'prep' => 8, 'featured' => true, 'img' => '/images/dishes/pho-bo.svg', 'desc' => 'Bò tái, nạm, bánh phở mềm, nước dùng hầm xương 12 tiếng.'],
                ['name' => 'Phở Gà Ta', 'price' => 55000, 'prep' => 8, 'featured' => false, 'img' => '/images/dishes/pho-ga.svg', 'desc' => 'Gà ta thịt chắc, da giòn vàng ươm.'],
                ['name' => 'Bún Chả Hà Nội', 'price' => 50000, 'prep' => 10, 'featured' => true, 'img' => '/images/dishes/bun-cha.svg', 'desc' => 'Chả viên nướng than hoa, nước mắm đu đủ cà rốt.'],
                ['name' => 'Cơm Sườn Bì Chả', 'price' => 55000, 'prep' => 7, 'featured' => true, 'img' => '/images/dishes/com-suon.svg', 'desc' => 'Sườn nướng mật ong, bì sợi, chả trứng hấp.'],
                ['name' => 'Mì Xào Bò Rau Cải', 'price' => 60000, 'prep' => 10, 'featured' => false, 'img' => '/images/dishes/mi-xao.svg', 'desc' => 'Mì trứng xào bò mềm, cải ngọt giòn.'],
            ],
            'Khai vị & Ăn kèm' => [
                ['name' => 'Nem Rán Giòn (4 cái)', 'price' => 35000, 'prep' => 6, 'featured' => true, 'img' => '/images/dishes/nem-ran.svg', 'desc' => 'Vỏ giòn rụm, nhân thịt nấm mộc nhĩ miến tàu.'],
                ['name' => 'Chả Giò Hải Sản (4 cuốn)', 'price' => 45000, 'prep' => 6, 'featured' => false, 'img' => '/images/dishes/cha-gio.svg', 'desc' => 'Tôm mực tươi sốt mayonnaise chấm tương ớt.'],
                ['name' => 'Trứng Ốp La Thêm', 'price' => 10000, 'prep' => 3, 'featured' => false, 'img' => '/images/dishes/trung-op-la.svg', 'desc' => 'Trứng gà ta chiên lòng đào.'],
            ],
            'Đồ uống' => [
                ['name' => 'Trà Đá Tinh Khiết', 'price' => 5000, 'prep' => 2, 'featured' => true, 'img' => '/images/dishes/tra-da.svg', 'desc' => 'Trà lài thơm mát.'],
                ['name' => 'Trà Tắc Mật Ong', 'price' => 20000, 'prep' => 3, 'featured' => true, 'img' => '/images/dishes/tra-tac.svg', 'desc' => 'Tắc tươi vắt mật ong hoa nhãn giải nhiệt.'],
                ['name' => 'Coca Cola Lon', 'price' => 18000, 'prep' => 1, 'featured' => false, 'img' => '/images/dishes/coca.svg', 'desc' => 'Nước ngọt có gas 320ml kèm ly đá.'],
                ['name' => 'Bia Sài Gòn Special', 'price' => 25000, 'prep' => 1, 'featured' => false, 'img' => '/images/dishes/bia-saigon.svg', 'desc' => 'Bia ướp lạnh.'],
            ],
            'Tráng miệng' => [
                ['name' => 'Chè Hạt Sen Long Nhãn', 'price' => 25000, 'prep' => 3, 'featured' => false, 'img' => '/images/dishes/che-sen.svg', 'desc' => 'Hạt sen thanh mát, long nhãn giòn ngọt.'],
                ['name' => 'Sữa Chua Nếp Cẩm', 'price' => 20000, 'prep' => 2, 'featured' => true, 'img' => '/images/dishes/sua-chua.svg', 'desc' => 'Nếp cẩm ủ men dẻo quánh cùng sữa chua.'],
            ]
        ];

        $dishMap = [];
        $sortOrder = 1;

        foreach ($catalog as $catName => $dishes) {
            $category = Category::firstOrCreate([
                'restaurant_id' => $restaurant->id,
                'name' => $catName,
                'sort_order' => $sortOrder++,
                'is_active' => true,
            ]);

            foreach ($dishes as $d) {
                $dish = Dish::updateOrCreate(
                    ['name' => $d['name']],
                    [
                        'category_id' => $category->id,
                        'price' => $d['price'],
                        'estimated_prep_time' => $d['prep'],
                        'is_featured' => $d['featured'],
                        'description' => $d['desc'],
                        'image_url' => $d['img'] ?? null,
                        'status' => 'available',
                        'unit' => 'phần',
                    ]
                );
                $dishMap[$d['name']] = $dish;
            }
        }

        // 3. Gán định lượng cho Phở Bò Tái Nạm (mỗi phần cần 0.15kg bò, 0.2kg bánh phở)
        if (isset($dishMap['Phở Bò Tái Nạm'], $ingredientMap['Thịt bò tươi'], $ingredientMap['Bánh phở tươi'])) {
            DishIngredient::updateOrCreate(
                ['dish_id' => $dishMap['Phở Bò Tái Nạm']->id, 'ingredient_id' => $ingredientMap['Thịt bò tươi']->id],
                ['quantity_needed' => 0.15, 'unit' => 'kg']
            );
            DishIngredient::updateOrCreate(
                ['dish_id' => $dishMap['Phở Bò Tái Nạm']->id, 'ingredient_id' => $ingredientMap['Bánh phở tươi']->id],
                ['quantity_needed' => 0.20, 'unit' => 'kg']
            );
        }

        // 4. Tạo luật gợi ý combo mặc định ban đầu: Phở bò -> Nem rán, Trà đá
        if (isset($dishMap['Phở Bò Tái Nạm'], $dishMap['Nem Rán Giòn (4 cái)'], $dishMap['Trà Đá Tinh Khiết'])) {
            ComboRule::updateOrCreate(
                ['antecedent_dish_id' => $dishMap['Phở Bò Tái Nạm']->id, 'consequent_dish_id' => $dishMap['Nem Rán Giòn (4 cái)']->id],
                ['support' => 0.35, 'confidence' => 0.65, 'lift' => 1.85, 'source' => 'manual', 'is_active' => true]
            );
            ComboRule::updateOrCreate(
                ['antecedent_dish_id' => $dishMap['Phở Bò Tái Nạm']->id, 'consequent_dish_id' => $dishMap['Trà Đá Tinh Khiết']->id],
                ['support' => 0.50, 'confidence' => 0.80, 'lift' => 1.40, 'source' => 'manual', 'is_active' => true]
            );
        }
    }
}
