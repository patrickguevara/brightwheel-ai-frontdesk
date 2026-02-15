<?php

namespace Database\Seeders;

use App\Models\KnowledgeBase;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $knowledge = [
            // Hours & Calendar
            [
                'category' => 'hours',
                'title' => 'Hours of Operation',
                'content' => 'Little Oaks is open Monday through Friday, 6:30 AM to 6:30 PM, year-round. Early drop-off begins at 6:30 AM. Standard hours are 7:00 AM – 6:00 PM. Extended care (6:00 PM – 6:30 PM) is included at no extra charge. Late pickup after 6:30 PM incurs a fee of $1.00 per minute per child, billed directly to your account.',
                'keywords' => ['hours', 'open', 'close', 'time', 'schedule', 'operating hours'],
            ],
            [
                'category' => 'hours',
                'title' => 'Holiday Closures 2025-2026',
                'content' => "Little Oaks is closed on the following days. Tuition is not adjusted for holiday closures:\n\n- New Year's Day: Wednesday, January 1, 2025\n- Martin Luther King Jr. Day: Monday, January 20, 2025\n- Presidents' Day: Monday, February 17, 2025\n- Memorial Day: Monday, May 26, 2025\n- Juneteenth: Thursday, June 19, 2025\n- Independence Day: Friday, July 4, 2025\n- Labor Day: Monday, September 1, 2025\n- Veterans Day: Tuesday, November 11, 2025\n- Thanksgiving Break: Thursday–Friday, Nov 27–28, 2025\n- Winter Break: Wednesday–Friday, Dec 24–26, 2025\n- New Year's Day: Thursday, January 1, 2026",
                'keywords' => ['holiday', 'closed', 'veterans day', 'thanksgiving', 'christmas', 'new year', 'mlk', 'memorial day', 'independence day', 'labor day', 'juneteenth', 'presidents day'],
            ],
            [
                'category' => 'hours',
                'title' => 'Inclement Weather Closures',
                'content' => 'The center may also close for up to 2 inclement weather days per year. Families will be notified via the Brightwheel app by 5:30 AM on any weather closure day. Little Oaks follows local school district closure decisions.',
                'keywords' => ['weather', 'snow', 'ice', 'closure', 'emergency'],
            ],

            // Tuition & Fees
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Infant Program',
                'content' => "Infant (6 weeks – 12 months) tuition rates:\n- 5-Day Full-Time: $1,850/month\n- 3-Day Part-Time: $1,295/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'infant', 'baby', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Young Toddler Program',
                'content' => "Young Toddler (12 – 18 months) tuition rates:\n- 5-Day Full-Time: $1,750/month\n- 3-Day Part-Time: $1,225/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'young toddler', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Toddler Program',
                'content' => "Toddler (18 – 24 months) tuition rates:\n- 5-Day Full-Time: $1,650/month\n- 3-Day Part-Time: $1,155/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'toddler', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Twos Program',
                'content' => "Twos (2 – 3 years) tuition rates:\n- 5-Day Full-Time: $1,500/month\n- 3-Day Part-Time: $1,050/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'twos', '2 year old', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Preschool Program',
                'content' => "Preschool (3 – 4 years) tuition rates:\n- 5-Day Full-Time: $1,350/month\n- 3-Day Part-Time: $945/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'preschool', '3 year old', '4 year old', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Tuition Rates - Pre-K Program',
                'content' => "Pre-K (4 – 5 years) tuition rates:\n- 5-Day Full-Time: $1,250/month\n- 3-Day Part-Time: $875/month\n\nRegistration fee: $150 per child (non-refundable, due at enrollment). Annual supply fee: $200 per child, due each August.",
                'keywords' => ['tuition', 'pre-k', 'prekindergarten', '4 year old', '5 year old', 'cost', 'price', 'fee', 'rates'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Payment Information',
                'content' => 'Tuition is due on the 1st of each month via autopay through Brightwheel. A $25 late fee is applied if payment is not received by the 5th. Payment methods: Auto-pay (ACH/credit card via parent portal), check, money order. Sibling discount: 10% off the lower tuition for the second child enrolled simultaneously.',
                'keywords' => ['payment', 'due', 'late fee', 'billing', 'sibling discount', 'autopay'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Childcare Subsidies',
                'content' => 'We accept Texas Workforce Commission (TWC) childcare subsidies. Families are responsible for any co-pays.',
                'keywords' => ['subsidy', 'assistance', 'twc', 'workforce commission', 'financial aid'],
            ],
            [
                'category' => 'tuition',
                'title' => 'Withdrawal Policy',
                'content' => 'Families must provide 30 calendar days\' written notice to withdraw. Tuition is due through the full notice period. Notice should be submitted in writing to the center director via email.',
                'keywords' => ['withdrawal', 'cancel', 'notice', 'leaving'],
            ],

            // Enrollment
            [
                'category' => 'enrollment',
                'title' => 'Enrollment Requirements',
                'content' => "To enroll your child, you must complete:\n- Enrollment application\n- Immunization records (Texas-required)\n- Medical and allergy information\n- Emergency contacts (minimum 2)\n- Signed handbook acknowledgment\n- Authorization forms (photo/media, field trip, sunscreen/insect repellent)\n\nNew enrollees have a two-week transition period.",
                'keywords' => ['enrollment', 'enroll', 'register', 'paperwork', 'requirements', 'immunization'],
            ],
            [
                'category' => 'enrollment',
                'title' => 'Waitlist',
                'content' => 'If your preferred program is full, you may join our waitlist for a $50 non-refundable fee. Waitlist priority is first-come, first-served, with siblings of current families given preference. Average wait time is 2–4 months for infants and 1–2 months for older classrooms.',
                'keywords' => ['waitlist', 'waiting list', 'full', 'capacity'],
            ],

            // Health & Illness
            [
                'category' => 'health',
                'title' => 'Illness Exclusion Policy',
                'content' => "To protect all children and staff, a child must stay home or will be sent home if they exhibit any of the following symptoms:\n\n- Fever of 100.4°F (38°C) or higher\n- Vomiting or diarrhea (2 or more episodes in 24 hours)\n- Undiagnosed rash or skin condition\n- Persistent cough, especially if accompanied by fever or colored nasal discharge\n- Pink eye (conjunctivitis) with discharge\n- Head lice (active, untreated infestation)\n- Any contagious illness diagnosed by a physician (strep, flu, RSV, hand-foot-mouth, COVID-19, etc.)\n\nChildren must be symptom-free for at least 24 hours (without fever-reducing medication) before returning to the center. For contagious illnesses, a doctor's return-to-care note may be required at the director's discretion.",
                'keywords' => ['sick', 'illness', 'fever', 'vomit', 'diarrhea', 'contagious', 'pink eye', 'strep', 'covid', 'rsv', 'hand foot mouth'],
            ],
            [
                'category' => 'health',
                'title' => 'Medication Administration',
                'content' => "Staff can administer prescription and over-the-counter medication only with:\n\n- A completed Medication Authorization Form (available at the front desk or in Brightwheel)\n- Medication in its original container with the child's name and dosage instructions\n- Written doctor's note for any prescription medication\n\nMedications are stored in a locked cabinet. Sunscreen and insect repellent may be applied with a signed blanket authorization on file.",
                'keywords' => ['medication', 'medicine', 'prescription', 'sunscreen', 'bug spray'],
            ],

            // Meals & Nutrition
            [
                'category' => 'meals',
                'title' => 'Meals Provided',
                'content' => 'Little Oaks participates in the USDA Child and Adult Care Food Program (CACFP). The following meals are provided at no additional cost: Breakfast (7:00 – 7:30 AM), Morning snack (9:00 – 9:15 AM), Lunch (11:00 – 11:30 AM), Afternoon snack (2:00 – 2:30 PM). Weekly menus are posted in each classroom, on the parent board near the front entrance, and shared via Brightwheel on Fridays for the following week.',
                'keywords' => ['meals', 'food', 'breakfast', 'lunch', 'snack', 'menu', 'cafcp'],
            ],
            [
                'category' => 'meals',
                'title' => 'Nut-Free Facility',
                'content' => 'Little Oaks is a peanut-aware facility. While we cannot guarantee a completely peanut-free environment, peanut and tree nut products are never served, and classrooms with enrolled children who have nut allergies are designated nut-free zones. Parents must not send peanut or tree nut products in lunchboxes.',
                'keywords' => ['nut free', 'peanut', 'allergy', 'allergies', 'tree nut'],
            ],
            [
                'category' => 'meals',
                'title' => 'Forgot Lunch',
                'content' => 'If a child arrives without a packed lunch and we are not able to reach a parent, we will provide a center meal and note it in Brightwheel. There is no additional charge for substitute meals. The center provides breakfast, lunch, and two snacks daily, included in tuition.',
                'keywords' => ['forgot lunch', 'no lunch', 'packed lunch', 'substitute meal'],
            ],
            [
                'category' => 'meals',
                'title' => 'Dietary Restrictions and Allergies',
                'content' => 'We take food allergies seriously. All allergies and dietary restrictions must be documented on the enrollment health form and communicated to your child\'s lead teacher. We accommodate common allergens (peanut/tree nut, dairy, egg, gluten, soy, shellfish), religious dietary requirements (halal, kosher, vegetarian), and medical dietary needs with a physician\'s note.',
                'keywords' => ['allergy', 'dietary restrictions', 'vegetarian', 'vegan', 'kosher', 'halal', 'gluten free'],
            ],

            // Daily Schedule
            [
                'category' => 'schedule',
                'title' => 'Daily Schedule',
                'content' => "Sample Daily Schedule (Preschool/Pre-K):\n\n- 6:30 – 7:30 AM: Arrival, free play, and breakfast\n- 7:30 – 8:00 AM: Morning circle (calendar, weather, songs)\n- 8:00 – 9:00 AM: Learning centers (literacy, math, science, art)\n- 9:00 – 9:15 AM: Morning snack\n- 9:15 – 10:15 AM: Outdoor play / gross motor\n- 10:15 – 11:00 AM: Small group instruction / projects\n- 11:00 – 11:30 AM: Lunch\n- 11:30 AM – 12:00 PM: Story time and wind-down\n- 12:00 – 2:00 PM: Rest/nap time\n- 2:00 – 2:30 PM: Wake up, afternoon snack\n- 2:30 – 3:30 PM: Enrichment (music, STEM, Spanish)\n- 3:30 – 4:30 PM: Outdoor play\n- 4:30 – 6:30 PM: Free play, quiet activities, and pickup\n\nInfant and toddler schedules follow individual feeding and nap routines while incorporating age-appropriate sensory, music, and movement activities throughout the day.",
                'keywords' => ['schedule', 'daily routine', 'what time', 'nap time', 'lunch time', 'outdoor play'],
            ],

            // Drop-off & Pick-up
            [
                'category' => 'pickup',
                'title' => 'Drop-Off Procedures',
                'content' => 'Drop-off is between 6:30 AM and 9:00 AM. Children arriving after 9:00 AM must be signed in at the front desk. All children must be signed in through the Brightwheel kiosk at the front entrance using the parent\'s 4-digit PIN or QR code. Walk your child to their classroom and ensure a teacher acknowledges the handoff. Breakfast is served until 7:30 AM. Children arriving after 7:30 AM should have already eaten breakfast.',
                'keywords' => ['drop off', 'drop-off', 'arrival', 'sign in', 'morning'],
            ],
            [
                'category' => 'pickup',
                'title' => 'Pick-Up Procedures',
                'content' => 'Pick-up is between 3:00 PM and 6:30 PM (or your contracted hours). Sign your child out through Brightwheel at the front entrance. Only authorized individuals listed on the enrollment form may pick up your child. Anyone not known to staff will be asked for a government-issued photo ID, which will be checked against the authorized list. Custodial agreements or court orders restricting pick-up must be provided in writing to the director.',
                'keywords' => ['pick up', 'pickup', 'authorized', 'photo id', 'departure'],
            ],
            [
                'category' => 'pickup',
                'title' => 'Late Pick-Up Fee',
                'content' => 'The center closes at 6:30 PM sharp. A late fee of $1.00 per minute per child applies after 6:30 PM, beginning at 6:31 PM. Late fees are billed through Brightwheel and added to your next invoice. After three late pick-ups in a 60-day period, a meeting with the director will be required to discuss a plan. Chronic late pick-ups may result in termination of enrollment.',
                'keywords' => ['late', 'late pickup', 'late fee', 'after hours'],
            ],

            // Classrooms
            [
                'category' => 'classrooms',
                'title' => 'Classroom Information',
                'content' => "Little Oaks has the following classrooms:\n\n- Infant (6 weeks – 12 months): Ratio 1:4, Max 8 children, Lead Teacher: Denise Okafor\n- Young Toddler (12 – 18 months): Ratio 1:5, Max 10 children\n- Toddler (18 – 24 months): Ratio 1:6, Max 12 children, Lead Teacher: Carla Mendez\n- Twos (2 – 3 years): Ratio 1:9, Max 18 children\n- Preschool (3 – 4 years): Ratio 1:11, Max 22 children\n- Pre-K (4 – 5 years): Ratio 1:13, Max 26 children, Lead Teacher: Aisha Johnson\n\nChildren transition to the next classroom based on age, developmental readiness, and space availability.",
                'keywords' => ['classroom', 'teacher', 'ratio', 'age group', 'infant', 'toddler', 'preschool', 'pre-k'],
            ],

            // Safety
            [
                'category' => 'safety',
                'title' => 'Safety and Security',
                'content' => 'Little Oaks maintains a secured entry with keypad code (changed quarterly). All visitors must sign in with photo ID required. Staff are background-checked (FBI + state) and CPR/First Aid certified. Monthly fire drills and quarterly tornado/lockdown drills are conducted. Emergency plans are posted in every room. In case of emergency evacuation, our designated off-site location is Maplewood Community Church, 800 Maplewood Drive (0.2 miles from the center).',
                'keywords' => ['safety', 'security', 'emergency', 'door code', 'background check', 'fire drill'],
            ],

            // Communication
            [
                'category' => 'general',
                'title' => 'Communication Channels',
                'content' => 'Brightwheel is our primary communication platform. Through the app, you can view daily activity reports, receive photos and videos, message teachers or the director, review and sign documents, make tuition payments, and check in/out your child. All families are required to download and activate their Brightwheel account during enrollment. Teachers respond to messages within 2 hours during operating hours; after-hours messages are addressed the next business day.',
                'keywords' => ['brightwheel', 'communication', 'app', 'message', 'contact'],
            ],

            // Policies
            [
                'category' => 'policies',
                'title' => 'What to Bring',
                'content' => "Daily items for all ages: A complete change of weather-appropriate clothing (labeled), closed-toe shoes suitable for outdoor play, a water bottle (labeled), sunscreen (if not already on blanket authorization).\n\nInfants (additional): Pre-measured, labeled bottles of breast milk or formula, diapers and wipes (at least a one-week supply), a blanket or sleep sack for nap time, pacifiers (if used; labeled), baby food or age-appropriate snacks as needed.\n\nToddlers & Preschoolers (additional): A fitted cot sheet and small blanket for rest time (sent home Fridays for laundering), pull-ups or diapers if not yet potty trained, and extra underwear/pants if potty training.",
                'keywords' => ['bring', 'what to bring', 'supplies', 'diapers', 'bottles', 'clothing'],
            ],
            [
                'category' => 'policies',
                'title' => 'Birthday Celebrations',
                'content' => 'We love celebrating birthdays! Families may bring a simple treat to share with the class (store-bought, with an ingredient label, and nut-free). Please coordinate with your child\'s teacher at least 2 days in advance. Non-food celebrations (a favorite book, stickers, or a special activity) are also welcome and encouraged.',
                'keywords' => ['birthday', 'celebration', 'party', 'treats'],
            ],
        ];

        foreach ($knowledge as $item) {
            KnowledgeBase::create($item);
        }
    }
}
