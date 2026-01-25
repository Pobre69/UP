import "../../Design/StarterPage/Box3.css";
import { BarChart3, Rocket, Target, LucideMegaphone, TrendingUp, Globe } from "lucide-react";

export default function Box3() {
    return (
        <div id="box3">
            <div id="Title-OqueFazemos">
                <h3>
                    O que <span className="text_purple_linear">fazemos</span> por você<br />
                    <div className="TitlePhrase">Oferecemos soluções completas para transformar e elevar seu negócio ao próximo nível com tecnologia e estratégia.</div>
                </h3>
            </div>
            <div id="BoxGrid">
                <div className="box3_1">
                    <div className="BoxContainer3">
                        <div className="BoxContent3">
                            <div className="icon-text-inline">
                                <div className="icon-holder">
                                    <BarChart3 className="icon"/>
                                </div>                        
                                <p className="ptitle2">Análise de Dados</p>
                            </div>
                            <br />
                            <p className="ptext2">Transforme dados em insights valiosos para decisões estratégicas.</p>
                        </div>
                    </div>
                    <div className="BoxContainer3">
                        <div className="BoxContent3">
                            <div className="icon-text-inline">
                                <div className="icon-holder">
                                    <Rocket className="icon"/>
                                </div>           
                                <p className="ptitle2">Marketing Digital</p>
                            </div>                      
                            <br />
                            <p className="ptext2">Estratégias personalizadas para impulsionar sua presença online.</p>
                        </div>
                    </div>
                    <div className="BoxContainer3">
                        <div className="BoxContent3">
                            <div className="icon-text-inline">
                                <div className="icon-holder">
                                    <Target className="icon"/>
                                </div>  
                                <p className="ptitle2">Consultoria</p>
                            </div>                        
                            <br />
                            <p className="ptext2">Orientação especializada para alcançar seus objetivos de negócio.</p>
                        </div>
                    </div>
                </div>
                <div className="box3_2">
                    <div className="BoxContainer3">
                        <div className="BoxContent3">
                            <div className="icon-text-inline">
                                <div className="icon-holder">
                                    <LucideMegaphone className="icon"/>                                
                                </div>
                                <p className="ptitle2">Tráfego Pago</p>
                            </div>                     
                            <br />
                            <p className="ptext2">Google Ads + Meta AI para alcançar seu público ideal.</p>
                        </div>
                    </div>
                    <div className="BoxContainer3">
                        <div className="BoxContent3">
                            <div className="icon-text-inline">
                                <div className="icon-holder">
                                    <TrendingUp className="icon"/>
                                </div>                 
                                <p className="ptitle2">Crescimento</p>
                            </div>                      
                            <br />
                            <p className="ptext2">Estratégias comprovadas para escalar seu negócio.</p>
                        </div>
                    </div>
                    <div className="BoxContainer3">
                        <div className="BoxContent3">
                            <div className="icon-text-inline">
                                <div className="icon-holder">
                                    <Globe className="icon"/>
                                </div>                        
                                <p className="ptitle2">Criação de Sites</p>
                            </div>                        
                            <br />
                            <p className="ptext2">Sites modernos e otimizados para converter visitantes em clientes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}