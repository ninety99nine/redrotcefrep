<?php

namespace Database\Seeders;

use App\Models\AiTopic;
use App\Models\AiLesson;
use Illuminate\Database\Seeder;

class AiTopicSeeder extends Seeder
{
    public function run()
    {
        // Foreach AI topic
        foreach ($this->getAiTopics() as $key => $aiTopic) {

            $lessons = $aiTopic['lessons'];
            unset($aiTopic['lessons']);

            $aiTopic['position'] = $key + 1;
            $aiTopic['name'] = ucfirst(strtolower($aiTopic['name']));

            // Create AI topic
            $topic = AiTopic::create($aiTopic);

            foreach ($lessons as $key2 => $aiLesson) {

                $aiLesson['position'] = $key2 + 1;
                $aiLesson['title'] = ucfirst(strtolower($aiLesson['title']));

                // Create AI lesson
                AiLesson::create(array_merge($aiLesson, ['ai_topic_id' => $topic->id]));

            }
        }
    }

    /**
     * Return the ai message categories
     *
     * @return array
     */
    public function getAiTopics()
    {
        $now = now()->toDateTimeString();

        return [
            [
                'name' => 'Growing Your Hustle',
                'system_prompt' => 'You are a business growth expert helping small business owners expand their operations. Provide practical, actionable advice tailored to entrepreneurs looking to scale their businesses efficiently and sustainably.',
                'lessons' => [
                    [
                        'title' => 'When to Expand',
                        'prompt' => 'How do I know when to expand my small business?',
                    ],
                    [
                        'title' => 'Getting Extra Help',
                        'prompt' => 'When should I hire someone to help with my business?',
                    ],
                    [
                        'title' => 'Adding New Products',
                        'prompt' => 'How do I decide when to add new products to my store?',
                    ],
                    [
                        'title' => 'Opening Another Store',
                        'prompt' => 'What are the benefits of opening another store location?',
                    ],
                    [
                        'title' => 'Scaling Without Risks',
                        'prompt' => 'How can I grow my business without taking on too much risk?',
                    ],
                ],
            ],
            [
                'name' => 'Marketing Made Easy',
                'system_prompt' => 'You are a marketing coach for small business owners. Offer simple, cost-effective strategies to promote their businesses, focusing on practical marketing techniques that yield results.',
                'lessons' => [
                    [
                        'title' => 'Getting Your Name Out',
                        'prompt' => 'How can I promote my small business on a budget?',
                    ],
                    [
                        'title' => 'Social Media Basics',
                        'prompt' => 'How do I use social media to promote my products?',
                    ],
                    [
                        'title' => 'Word of Mouth',
                        'prompt' => 'How do I encourage my customers to spread the word about my business?',
                    ],
                    [
                        'title' => 'Building a Brand',
                        'prompt' => 'What does it mean to build a brand for my business?',
                    ],
                    [
                        'title' => 'Creating Simple Ads',
                        'prompt' => 'How can I create simple, low-cost ads for my products?',
                    ],
                ],
            ],
            [
                'name' => 'Money Smart',
                'system_prompt' => 'You are a financial advisor for small business owners. Provide straightforward advice on budgeting, expense tracking, and financial planning to ensure business profitability and stability.',
                'lessons' => [
                    [
                        'title' => 'Budgeting Basics',
                        'prompt' => 'What are some simple tips for budgeting in my business?',
                    ],
                    [
                        'title' => 'Tracking Expenses',
                        'prompt' => 'How do I keep track of my daily expenses effectively?',
                    ],
                    [
                        'title' => 'Saving for Growth',
                        'prompt' => 'How much should I save to grow my business?',
                    ],
                    [
                        'title' => 'Avoiding Debt',
                        'prompt' => 'What are the best ways to avoid debt in my business?',
                    ],
                    [
                        'title' => 'Simple Bookkeeping',
                        'prompt' => 'What is simple bookkeeping and why is it important for my business?',
                    ],
                ],
            ],
            [
                'name' => 'Keeping Customers Happy',
                'system_prompt' => 'You are a customer service expert for small businesses. Share practical tips to enhance customer satisfaction, handle complaints, and build trust to foster loyalty.',
                'lessons' => [
                    [
                        'title' => 'Handling Complaints',
                        'prompt' => 'How do I handle customer complaints and turn them into positive experiences?',
                    ],
                    [
                        'title' => 'Building Trust',
                        'prompt' => 'How can I build trust with my customers?',
                    ],
                    [
                        'title' => 'Offering Discounts',
                        'prompt' => 'How do I offer discounts without losing profit?',
                    ],
                    [
                        'title' => 'Improving Service',
                        'prompt' => 'What are some simple ways to improve customer service?',
                    ],
                    [
                        'title' => 'Following Up',
                        'prompt' => 'How important is it to follow up with my customers?',
                    ],
                ],
            ],
            [
                'name' => 'Keeping Customers Coming Back',
                'system_prompt' => 'You are a loyalty program specialist for small businesses. Provide actionable strategies to retain customers through rewards, personalized service, and consistent engagement.',
                'lessons' => [
                    [
                        'title' => 'Customer Loyalty',
                        'prompt' => 'How do I create a customer loyalty program?',
                    ],
                    [
                        'title' => 'Offering Rewards',
                        'prompt' => 'What types of rewards can I offer to keep my customers coming back?',
                    ],
                    [
                        'title' => 'Personalized Service',
                        'prompt' => 'How can I provide personalized service to my customers?',
                    ],
                    [
                        'title' => 'Thanking Customers',
                        'prompt' => 'What are simple ways to thank customers for their business?',
                    ],
                    [
                        'title' => 'Reaching Out Again',
                        'prompt' => 'How often should I reach out to customers who haven’t bought in a while?',
                    ],
                ],
            ],
            [
                'name' => 'Winning Negotiations',
                'system_prompt' => 'You are a negotiation coach for small business owners. Offer practical advice on negotiating with suppliers, customers, and partners to achieve favorable outcomes.',
                'lessons' => [
                    [
                        'title' => 'Negotiating With Suppliers',
                        'prompt' => 'How do I negotiate better deals with suppliers?',
                    ],
                    [
                        'title' => 'Setting Prices',
                        'prompt' => 'How do I negotiate the right price without losing customers?',
                    ],
                    [
                        'title' => 'Closing Deals',
                        'prompt' => 'What’s the best way to close a deal with a customer?',
                    ],
                    [
                        'title' => 'Being Confident',
                        'prompt' => 'How can I be more confident when negotiating prices?',
                    ],
                    [
                        'title' => 'Staying Firm',
                        'prompt' => 'How do I stand firm on prices without losing customers?',
                    ],
                ],
            ],
            [
                'name' => 'Getting the Best from Suppliers',
                'system_prompt' => 'You are a supply chain expert for small businesses. Provide guidance on selecting suppliers, building relationships, and negotiating terms to optimize supply chain efficiency.',
                'lessons' => [
                    [
                        'title' => 'Choosing Suppliers',
                        'prompt' => 'How do I choose the right supplier for my products?',
                    ],
                    [
                        'title' => 'Building Supplier Relationships',
                        'prompt' => 'How can I build long-term relationships with my suppliers?',
                    ],
                    [
                        'title' => 'Negotiating Terms',
                        'prompt' => 'How do I negotiate better payment terms with suppliers?',
                    ],
                    [
                        'title' => 'Finding Reliable Suppliers',
                        'prompt' => 'Where can I find reliable suppliers for my business?',
                    ],
                    [
                        'title' => 'Managing Supplier Disputes',
                        'prompt' => 'What’s the best way to handle disputes with suppliers?',
                    ],
                ],
            ],
            [
                'name' => 'Boosting Your Sales',
                'system_prompt' => 'You are a sales strategist for small businesses. Share effective techniques to increase sales, attract new customers, and maximize revenue through upselling and cross-selling.',
                'lessons' => [
                    [
                        'title' => 'Upselling Techniques',
                        'prompt' => 'How can I upsell to my customers effectively?',
                    ],
                    [
                        'title' => 'Cross-Selling',
                        'prompt' => 'What is cross-selling and how do I use it to increase sales?',
                    ],
                    [
                        'title' => 'Seasonal Sales',
                        'prompt' => 'How can I take advantage of seasonal sales opportunities?',
                    ],
                    [
                        'title' => 'Attracting New Customers',
                        'prompt' => 'What are simple ways to attract new customers to my business?',
                    ],
                    [
                        'title' => 'Increasing Average Order Value',
                        'prompt' => 'How do I increase the average amount customers spend in my store?',
                    ],
                ],
            ],
            [
                'name' => 'Smart Inventory Management',
                'system_prompt' => 'You are an inventory management expert for small businesses. Provide practical advice on managing stock levels, avoiding overstock, and organizing inventory efficiently.',
                'lessons' => [
                    [
                        'title' => 'Avoiding Overstock',
                        'prompt' => 'How do I avoid overstocking products that don’t sell?',
                    ],
                    [
                        'title' => 'Managing Stock Levels',
                        'prompt' => 'What’s the best way to keep track of my stock levels?',
                    ],
                    [
                        'title' => 'When to Reorder',
                        'prompt' => 'How do I know when to reorder products to avoid running out?',
                    ],
                    [
                        'title' => 'Dealing with Slow-Moving Products',
                        'prompt' => 'How can I sell products that aren’t moving fast?',
                    ],
                    [
                        'title' => 'Organizing My Inventory',
                        'prompt' => 'What are some simple ways to organize my inventory better?',
                    ],
                ],
            ],
            [
                'name' => 'Customer Service That Shines',
                'system_prompt' => 'You are a customer service coach for small businesses. Offer strategies to handle difficult customers, improve communication, and create memorable customer experiences.',
                'lessons' => [
                    [
                        'title' => 'Handling Difficult Customers',
                        'prompt' => 'What’s the best way to handle difficult customers?',
                    ],
                    [
                        'title' => 'Offering Support',
                        'prompt' => 'How do I offer great support even after a sale?',
                    ],
                    [
                        'title' => 'Creating Positive Experiences',
                        'prompt' => 'What can I do to create a positive experience for every customer?',
                    ],
                    [
                        'title' => 'Communicating Clearly',
                        'prompt' => 'How can I improve communication with my customers?',
                    ],
                    [
                        'title' => 'Going the Extra Mile',
                        'prompt' => 'What does it mean to go the extra mile for customers?',
                    ],
                ],
            ],
            [
                'name' => 'Effective Time Management',
                'system_prompt' => 'You are a time management expert for entrepreneurs. Provide practical tips to prioritize tasks, delegate effectively, and balance work and personal life for business owners.',
                'lessons' => [
                    [
                        'title' => 'Prioritizing Tasks',
                        'prompt' => 'How do I prioritize tasks in my business to save time?',
                    ],
                    [
                        'title' => 'Avoiding Burnout',
                        'prompt' => 'How do I avoid burnout while managing my business?',
                    ],
                    [
                        'title' => 'Delegating Tasks',
                        'prompt' => 'When and how should I delegate tasks to others?',
                    ],
                    [
                        'title' => 'Balancing Work and Personal Life',
                        'prompt' => 'How can I better balance my work and personal life?',
                    ],
                    [
                        'title' => 'Making Time for Growth',
                        'prompt' => 'How do I make time to work on growing my business?',
                    ],
                ],
            ],
            [
                'name' => 'Money Matters for Entrepreneurs',
                'system_prompt' => 'You are a financial strategist for small business owners. Share actionable advice on managing cash flow, cutting costs, and securing funding to ensure financial success.',
                'lessons' => [
                    [
                        'title' => 'Managing Cash Flow',
                        'prompt' => 'How can I better manage cash flow in my business?',
                    ],
                    [
                        'title' => 'Saving for Emergencies',
                        'prompt' => 'Why is it important to save for business emergencies?',
                    ],
                    [
                        'title' => 'Cutting Unnecessary Costs',
                        'prompt' => 'What are some ways to cut unnecessary costs in my business?',
                    ],
                    [
                        'title' => 'Getting Small Loans',
                        'prompt' => 'How do I apply for a small business loan or grant?',
                    ],
                    [
                        'title' => 'Making Profits',
                        'prompt' => 'What are some strategies to ensure my business is profitable?',
                    ],
                ],
            ],
            [
                'name' => 'Dealing with Competition',
                'system_prompt' => 'You are a competitive strategy expert for small businesses. Provide guidance on standing out in the market, learning from competitors, and attracting customers effectively.',
                'lessons' => [
                    [
                        'title' => 'Standing Out in the Market',
                        'prompt' => 'How can my business stand out in a competitive market?',
                    ],
                    [
                        'title' => 'Learning from Competitors',
                        'prompt' => 'What can I learn from my competitors to improve my business?',
                    ],
                    [
                        'title' => 'Winning Over Competitor’s Customers',
                        'prompt' => 'How do I attract customers from my competitors?',
                    ],
                    [
                        'title' => 'Offering Something Unique',
                        'prompt' => 'What can I do to offer something unique that my competitors don’t have?',
                    ],
                    [
                        'title' => 'Competing on Price vs Value',
                        'prompt' => 'Should I compete with others on price or value? What’s better?',
                    ],
                ],
            ],
            [
                'name' => 'Building Business Networks',
                'system_prompt' => 'You are a networking expert for small business owners. Offer practical advice on building professional relationships, finding partners, and leveraging networks for growth.',
                'lessons' => [
                    [
                        'title' => 'Why Networking Matters',
                        'prompt' => 'Why is building a business network important for my growth?',
                    ],
                    [
                        'title' => 'Finding Business Partners',
                        'prompt' => 'How do I find reliable business partners or collaborators?',
                    ],
                    [
                        'title' => 'Joining Local Business Groups',
                        'prompt' => 'What are the benefits of joining local business groups?',
                    ],
                    [
                        'title' => 'Networking Tips',
                        'prompt' => 'How can I improve my networking skills?',
                    ],
                    [
                        'title' => 'Networking Tips',
                        'prompt' => 'How do I build long-lasting business relationships?',
                    ],
                ]
            ]
        ];
    }
}
