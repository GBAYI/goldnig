<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Legull_Conf' ) ) {
	class Legull_Conf {
		private static $_this;

		public $addons = array(
			array(
				'name'        => 'Digital Ecommerce',
				'remote_url'  => 'http://www.legull.com/addon/digital-ecommerce/',
				'description' => 'Do you sell downloads? For your apps, software, or content downloads, set a firm foundation for your sales with Legull’s simple Q&A that will generate the terms for your site.  Change your policies?  Change your answers to the questions and change your terms. This add-on provides terms of service for your sales transactions, as well as downloading, rights management, product descriptions, copyright notices, limitations of liability for you, and sharing limitations for your users.  It also will guide your buyers on what to do if they have a complaint, question, or need help.',
				'active'      => false
			),
			array(
				'name'        => 'Product Ecommerce',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'If you sell physical goods (things that are packed and shipped), use the Q&A format of this add-on to quickly define your terms with your customers.  These terms address return or exchange policies, any warranty information, payment disputes and other disputes, limitations of liability, product description disclaimers, and other terms to keep you and your buyers on the same page.',
				'active'      => false
			),
			array(
				'name'        => 'Auction/Deal Ecommerce',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'Auction and deals web sites have their own ways of doing business, which require additional terms and conditions.  Legull’s Q&A auction and deal site terms generator include return or exchange policies, any warranty information, payment disputes and other disputes, limitations of liability, product description disclaimers, and other other limiting and protective terms that are specific to deal and auction sites.',
				'active'      => false
			),
			array(
				'name'        => 'Classified Ads Ecommerce',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'If you host ads that are posted by others, you are going to need some disclaimers, posting policies, and limitations of liability.  You should also have copyright notice provisions, and community guidelines to manage any objectionable content, products, services, or behavior.  Get those things here, with the Legull Classifieds add-on.',
				'active'      => false
			),
			array(
				'name'        => 'Digital Membership',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'Selling digital memberships to your site? Whether it’s a paywall for your content or a data subscription, you will want a clear statement of the rights of access your users are buying, and limitations on those rights. (The right to access is not the same as the right to copy or re-blog.)  Legull’s digital membership terms add-on will help to protect your walled garden.',
				'active'      => false
			),
			array(
				'name'        => 'Social Networking',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'If your site has a social networking aspect to it, you need good terms. Users will be users, and your site will need some guardrails to keep things safely on the road.  Harassment, infringing content, misleading usernames or profile pictures — who knows what people will get up to. The Legull social add-on will guide you through it, setting your rules as you complete the simple Q&A.',
				'active'      => false
			),
			array(
				'name'        => 'User Generated/Upload',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'U got UGC?  User-generated content can make your site sing. But the noise from potential liabilities and user management can make your ears ring. How do you handle takedown requests?  Are you on the hook for illicit, illegal, or just plain awful decisions of your users?  Not if we can help it.  The Legull UGC add-on will get you to a safer place.  Click through a few questions and get to a much safer harbor.',
				'active'      => false
			),
			array(
				'name'        => 'News & Information',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'Everyone with a blog can be a reporter, but not everyone can do it well.  If your reporting matters to you, you can put your site on a firmer footing with a strong set of terms, using the Legull Q&A to generate terms and policies for your corner of the fourth estate.',
				'active'      => false
			),
			array(
				'name'        => 'Review & Opinion',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'Opinions are universal, and product and service reviews are extremely useful.  But if you host enough of them, you will need a good set of terms and conditions for your site.  Limit your liability, reserve your right to manage and amend site content, and provide some rules of the road for your readers and content providers.  Legull’s Review and Opinion terms add-on will help you get there quickly and easily.',
				'active'      => false
			),
			array(
				'name'        => 'Dating',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'Dating and match-making sites present a unique set of challenges and risks for the site owner.  Legull’s dating site add-on will generate terms that cover the security, user-generated content, user conduct, intellectual property and privacy rights, paid membership, limitations of site owner liability, and other aspects of your site’s work to play cupid for your users.',
				'active'      => false
			),
			array(
				'name'        => 'Curation',
				'remote_url'  => 'http://www.legull.com/addons/',
				'description' => 'Sites that work to organize and connect the content of other sites are an important online discovery service.  They also present their own challenges for policies, procedures, and legal compliance.  The Legull Curation terms set will use a simple question and answer format to guide you to your own terms and policies.',
				'active'      => false
			)
		);

		public static function retrieve() {
			if ( !isset( self::$_this ) ) {
				$className   = __CLASS__;
				self::$_this = new $className;
			}

			return self::$_this;
		}
	}
}