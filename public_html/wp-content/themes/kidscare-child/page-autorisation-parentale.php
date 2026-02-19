<?php
/**
 * Template Name: Autorisation parentale
 *
 * Custom template used to capture parental authorization waivers.
 */

list( $form_data, $form_errors, $form_success ) = kidscare_handle_parental_authorization_submission();

$script_path = get_stylesheet_directory() . '/js/parental-authorization-minors.js';
wp_enqueue_script(
    'kidscare-parental-authorization-minors',
    get_stylesheet_directory_uri() . '/js/parental-authorization-minors.js',
    array(),
    file_exists( $script_path ) ? filemtime( $script_path ) : null,
    true
);

get_header();

$waiver_text = <<<TEXT
AUTORISATION PARENTALE ET DÉCHARGE DE RESPONSABILITÉ

Activités couvertes
Les activités offertes par Le Petit Kangaroo incluent notamment des ateliers moteurs, des jeux supervisés, des parcours d'habiletés ainsi que tout autre programme récréatif proposé dans ses locaux ou lors de sorties encadrées.

Reconnaissance des risques
Je reconnais que la participation de mon enfant aux activités décrites ci-dessus comporte des risques inhérents, y compris, sans s'y limiter, des chutes, collisions, entorses, fractures, réactions allergiques ou autres blessures pouvant survenir malgré une supervision adéquate.

Acceptation et renonciation
Je consens volontairement à la participation de mon enfant aux activités de Le Petit Kangaroo et j'assume l'entière responsabilité des risques associés. Je renonce à toute réclamation, poursuite ou demande contre Le Petit Kangaroo, ses propriétaires, administrateurs, employés, bénévoles, représentants et partenaires relativement à toute blessure, perte ou dommage subi par mon enfant, sauf en cas de négligence grave ou de faute intentionnelle.

État de santé et obligations
Je déclare que mon enfant est en bonne santé, apte à participer aux activités proposées et exempt de toute condition médicale non divulguée qui pourrait être aggravée par sa participation. Je m'engage à informer Le Petit Kangaroo de toute condition médicale, allergie, besoin particulier ou médication pertinente et à fournir les traitements, équipements ou instructions nécessaires.

Soins médicaux d'urgence
En cas d'accident ou de situation nécessitant des soins immédiats, j'autorise Le Petit Kangaroo à administrer les premiers soins et, si nécessaire, à obtenir les services de professionnels de la santé. J'accepte d'assumer l'ensemble des frais liés aux soins médicaux requis pour mon enfant.

Utilisation d'images
J'autorise Le Petit Kangaroo à capter et à utiliser des photographies, enregistrements vidéo ou audio de mon enfant pris dans le cadre des activités à des fins promotionnelles, éducatives ou informatives, sur tout support, sans compensation, sauf avis écrit contraire transmis avant les activités.

Déclaration finale
Je certifie avoir lu et compris l'intégralité de la présente autorisation parentale et décharge de responsabilité. Je confirme avoir eu l'occasion de poser des questions et d'obtenir des réponses satisfaisantes. En signant ci-dessous, j'accepte l'ensemble des conditions et confirme que les renseignements fournis sont exacts.

TEXT;

if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();

        $kidscare_seo = kidscare_is_on( kidscare_get_theme_option( 'seo_snippets' ) );
        ?>

        <main id="primary" class="parental-authorization">
            <div class="parental-authorization__inner">
                <article id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>"
                    <?php
                    post_class( 'post_item_single post_type_page' );
                    if ( $kidscare_seo ) {
                        ?>
                        itemscope="itemscope"
                        itemprop="mainEntityOfPage"
                        itemtype="//schema.org/<?php echo esc_attr( kidscare_get_markup_schema() ); ?>"
                        itemid="<?php echo esc_url( get_the_permalink() ); ?>"
                        content="<?php the_title_attribute( '' ); ?>"
                        <?php
                    }
                    ?>
                >
                    <div class="post_content entry-content">
                        <?php if ( $form_success ) : ?>
                            <div class="parental-authorization__notice parental-authorization__notice--success">
                                <?php esc_html_e( 'Merci! Votre autorisation parentale a été soumise avec succès.', 'kidscare-child' ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $form_errors['general'] ) ) : ?>
                            <div class="parental-authorization__notice parental-authorization__notice--error">
                                <?php echo esc_html( $form_errors['general'] ); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="parental-authorization__form">
                            <?php wp_nonce_field( 'kidscare_parental_authorization', 'kidscare_parental_authorization_nonce' ); ?>

                            <div class="parental-authorization__fields">
                                <section class="parental-authorization__section parental-authorization__section--parent">
                                    <h2 class="parental-authorization__section-title"><?php esc_html_e( 'Informations sur le parent ou tuteur', 'kidscare-child' ); ?></h2>

                                    <div class="parental-authorization__section-grid">
                                        <div class="form-field">
                                            <label for="parent-first-name"><?php esc_html_e( 'Prénom du parent ou tuteur', 'kidscare-child' ); ?></label>
                                            <input
                                                type="text"
                                                id="parent-first-name"
                                                name="parent_first_name"
                                                value="<?php echo esc_attr( $form_data['parent_first_name'] ); ?>"
                                                required
                                            />
                                            <?php if ( ! empty( $form_errors['parent_first_name'] ) ) : ?>
                                                <p class="form-error"><?php echo esc_html( $form_errors['parent_first_name'] ); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="form-field">
                                            <label for="parent-last-name"><?php esc_html_e( 'Nom de famille du parent ou tuteur', 'kidscare-child' ); ?></label>
                                            <input
                                                type="text"
                                                id="parent-last-name"
                                                name="parent_last_name"
                                                value="<?php echo esc_attr( $form_data['parent_last_name'] ); ?>"
                                                required
                                            />
                                            <?php if ( ! empty( $form_errors['parent_last_name'] ) ) : ?>
                                                <p class="form-error"><?php echo esc_html( $form_errors['parent_last_name'] ); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="form-field">
                                            <label for="parent-email"><?php esc_html_e( 'Adresse courriel', 'kidscare-child' ); ?></label>
                                            <input
                                                type="email"
                                                id="parent-email"
                                                name="parent_email"
                                                value="<?php echo esc_attr( $form_data['parent_email'] ); ?>"
                                                required
                                            />
                                            <?php if ( ! empty( $form_errors['parent_email'] ) ) : ?>
                                                <p class="form-error"><?php echo esc_html( $form_errors['parent_email'] ); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="form-field">
                                            <label for="parent-phone"><?php esc_html_e( 'Numéro de téléphone', 'kidscare-child' ); ?></label>
                                            <input
                                                type="tel"
                                                id="parent-phone"
                                                name="parent_phone"
                                                value="<?php echo esc_attr( $form_data['parent_phone'] ); ?>"
                                            />
                                            <?php if ( ! empty( $form_errors['parent_phone'] ) ) : ?>
                                                <p class="form-error"><?php echo esc_html( $form_errors['parent_phone'] ); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </section>

                                <section class="parental-authorization__section parental-authorization__section--minors" data-minors-wrapper>
                                    <div class="parental-authorization__section-header">
                                        <h2 class="parental-authorization__section-title"><?php esc_html_e( 'Enfants concernés', 'kidscare-child' ); ?></h2>

                                        <button type="button" class="button button-secondary minors-repeater__add" data-add-minor>
                                            <?php esc_html_e( 'Ajouter un enfant', 'kidscare-child' ); ?>
                                        </button>
                                    </div>

                                    <p class="parental-authorization__helper">
                                        <br/>
                                        <?php esc_html_e( 'Veuillez fournir les informations pour chaque enfant participant.', 'kidscare-child' ); ?>
                                    </p>

                                    <?php if ( ! empty( $form_errors['minors']['general'] ) ) : ?>
                                        <p class="form-error form-error--block"><?php echo esc_html( $form_errors['minors']['general'] ); ?></p>
                                    <?php endif; ?>

                                    <div class="minors-repeater" data-minors>
                                        <div class="minors-repeater__items" data-minors-items>
                                            <?php
                                            $remove_label_template = __( "Retirer l'enfant %d", 'kidscare-child' );

                                            foreach ( $form_data['minors'] as $index => $minor ) :
                                                $minor_errors = isset( $form_errors['minors'][ $index ] ) ? $form_errors['minors'][ $index ] : array();
                                                $first_name   = isset( $minor['first_name'] ) ? $minor['first_name'] : '';
                                                $last_name    = isset( $minor['last_name'] ) ? $minor['last_name'] : '';
                                                $birth_date   = isset( $minor['birth_date'] ) ? $minor['birth_date'] : '';
                                                ?>
                                                <fieldset class="minor-card" data-minor>
                                                    <legend class="minor-card__title">
                                                        <?php esc_html_e( 'Enfant', 'kidscare-child' ); ?>
                                                        <span data-minor-number><?php echo esc_html( $index + 1 ); ?></span>
                                                    </legend>
                                                    <br/>
                                                    <div class="minor-card__grid">
                                                        <div class="form-field">
                                                            <label for="minor-<?php echo esc_attr( $index ); ?>-first-name"><?php esc_html_e( 'Prénom', 'kidscare-child' ); ?></label>
                                                            <input
                                                                type="text"
                                                                id="minor-<?php echo esc_attr( $index ); ?>-first-name"
                                                                name="minors[<?php echo esc_attr( $index ); ?>][first_name]"
                                                                value="<?php echo esc_attr( $first_name ); ?>"
                                                                required
                                                            />
                                                            <?php if ( ! empty( $minor_errors['first_name'] ) ) : ?>
                                                                <p class="form-error"><?php echo esc_html( $minor_errors['first_name'] ); ?></p>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="form-field">
                                                            <label for="minor-<?php echo esc_attr( $index ); ?>-last-name"><?php esc_html_e( 'Nom de famille', 'kidscare-child' ); ?></label>
                                                            <input
                                                                type="text"
                                                                id="minor-<?php echo esc_attr( $index ); ?>-last-name"
                                                                name="minors[<?php echo esc_attr( $index ); ?>][last_name]"
                                                                value="<?php echo esc_attr( $last_name ); ?>"
                                                                required
                                                            />
                                                            <?php if ( ! empty( $minor_errors['last_name'] ) ) : ?>
                                                                <p class="form-error"><?php echo esc_html( $minor_errors['last_name'] ); ?></p>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="form-field">
                                                            <label for="minor-<?php echo esc_attr( $index ); ?>-birth-date"><?php esc_html_e( 'Date de naissance', 'kidscare-child' ); ?></label>
              

                                                            <input 
                                                              type="date" 
                                                              id="minor-<?php echo esc_attr( $index ); ?>-birth-date" 
                                                              name="minors[<?php echo esc_attr( $index ); ?>][birth_date]" 
                                                              data-placeholder="JJ/MM/AAAA" 
                                                              value="<?php echo esc_attr( $birth_date ); ?>"
                                                              required 
                                                              />

                                                            <?php if ( ! empty( $minor_errors['birth_date'] ) ) : ?>
                                                                <p class="form-error"><?php echo esc_html( $minor_errors['birth_date'] ); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <button
                                                        type="button"
                                                        class="button button--link minor-card__remove"
                                                        data-minor-remove
                                                        data-remove-label-template="<?php echo esc_attr( $remove_label_template ); ?>"
                                                        aria-label="<?php echo esc_attr( sprintf( $remove_label_template, $index + 1 ) ); ?>"
                                                    >
                                                        <?php esc_html_e( 'Retirer', 'kidscare-child' ); ?>
                                                    </button>
                                                </fieldset>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <template id="kidscare-minor-template">
                                        <fieldset class="minor-card" data-minor>
                                            <legend class="minor-card__title">
                                                <?php esc_html_e( 'Enfant', 'kidscare-child' ); ?>
                                                <span data-minor-number></span>
                                            </legend>
                                            <br />
                                            <div class="minor-card__grid">
                                                <div class="form-field">
                                                    <label for="minor-__index__-first-name"><?php esc_html_e( 'Prénom', 'kidscare-child' ); ?></label>
                                                    <input
                                                        type="text"
                                                        id="minor-__index__-first-name"
                                                        name="minors[__index__][first_name]"
                                                        value=""
                                                        required
                                                    />
                                                </div>

                                                <div class="form-field">
                                                    <label for="minor-__index__-last-name"><?php esc_html_e( 'Nom de famille', 'kidscare-child' ); ?></label>
                                                    <input
                                                        type="text"
                                                        id="minor-__index__-last-name"
                                                        name="minors[__index__][last_name]"
                                                        value=""
                                                        required
                                                    />
                                                </div>

                                                <div class="form-field">
                                                    <label for="minor-__index__-birth-date"><?php esc_html_e( 'Date de naissance', 'kidscare-child' ); ?></label>
                                                  <!--   <input
                                                        type="date"
                                                        id="minor-__index__-birth-date"
                                                        name="minors[__index__][birth_date]"
                                                        value=""
                                                        required
                                                    /> -->
                                                    <input 
                                                              type="date" 
                                                              id="minor-__index__-birth-date" 
                                                              name="minors[__index__][birth_date]" 
                                                              data-placeholder="JJ/MM/AAAA" 
                                                              value=""
                                                              required 
                                                              />
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="button button--link minor-card__remove"
                                                data-minor-remove
                                                data-remove-label-template="<?php echo esc_attr( __( "Retirer l'enfant %d", 'kidscare-child' ) ); ?>"
                                            >
                                                <?php esc_html_e( 'Retirer', 'kidscare-child' ); ?>
                                            </button>
                                        </fieldset>
                                    </template>
                                </section>
                            </div>

                            <div class="waiver-section">
                                <label for="waiver-terms" class="waiver-section__label"><?php esc_html_e( 'Décharge de responsabilité', 'kidscare-child' ); ?></label>
                                <br/><br/>
                                <textarea
                                    id="waiver-terms"
                                    class="waiver-terms"
                                    rows="28"
                                    readonly
                                    aria-readonly="true" style="width:100%"
                                ><?php echo esc_textarea( $waiver_text ); ?></textarea>
<br/><br/>
                                <label class="waiver-section__checkbox">
                                    <input
                                        type="checkbox"
                                        name="adult_terms_accept"
                                        value="1"
                                        required
                                        <?php checked( $form_data['adult_terms_accept'], '1' ); ?>
                                    />
                                    
                                    <span><?php esc_html_e( 'J’accepte les termes et conditions de la décharge de responsabilité.', 'kidscare-child' ); ?></span>
                                </label>
                                <?php if ( ! empty( $form_errors['adult_terms_accept'] ) ) : ?>
                                    <p class="form-error"><?php echo esc_html( $form_errors['adult_terms_accept'] ); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="form-field form-field--checkbox">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="consent_guardian"
                                        value="1"
                                        required
                                        <?php checked( $form_data['consent_guardian'], '1' ); ?>
                                    />
                                    <span><?php esc_html_e( 'Je confirme être le parent ou tuteur légal et autorise la participation de l\'enfant aux activités.', 'kidscare-child' ); ?></span>
                                </label>
                                <?php if ( ! empty( $form_errors['consent_guardian'] ) ) : ?>
                                    <p class="form-error"><?php echo esc_html( $form_errors['consent_guardian'] ); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="button button-primary">
                                    <?php esc_html_e( 'Soumettre l’autorisation', 'kidscare-child' ); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </div>
        </main>

        <?php
    endwhile;
endif;

get_footer();
